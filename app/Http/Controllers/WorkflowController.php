<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Models\Proyecto;
use App\Models\Registro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use ProcessMaker\Laravel\Facades\Nayra;
use ProcessMaker\Laravel\Repositories\InstanceRepository;
use ProcessMaker\Nayra\Bpmn\Events\ActivityActivatedEvent;
use ProcessMaker\Nayra\Bpmn\Models\Error;
use ProcessMaker\Nayra\Engine\ExecutionInstance;

class WorkflowController extends Controller
{
    use ResourceTrait;

    public function __construct()
    {
        app('events')->listen('ActivityActivated', function (ActivityActivatedEvent $event) {
            $users = $event->token->getProperty('user', []);
            $proyecto = $event->token->getInstance()->getDataStore()->getData('proyecto');
            if (!$proyecto || empty($proyecto['id'])) {
                return;
            }
            $proyecto = Proyecto::find($proyecto['id']);
            if ($users) {
                $users = is_array($users) ? $users : [$users];
                $proyecto->asignado_a = $users;
            } else {
                $proyecto->asignado_a = null;
            }
            $proyecto->save();
        });
    }

    private function encodeResponse(ExecutionInstance $instance)
    {
        $data = $instance->getDataStore()->getData();
        $tokens = InstanceRepository::dumpTokens($instance->getTokens());
        $home = Nayra::getPerformerByTypeName($instance->getProcess(), 'performer', 'home', $data);
        $response = [
            'instance' => $instance->getId(),
            'home' => $home,
            'tokens' => $tokens,
            'data' => (object) $data,
        ];
        $error = $instance->getProperty('error');
        if ($error) {
            $response['error'] = $error instanceof Error ? $error->getName() : $error;
        }
        return response()->json($response, $error ? 500 : 200);
    }

    /**
     * Start a new bpmn process.
     *
     * @param  string  $process
     *
     * @return \Illuminate\Http\Response
     */
    public function start($process, $startEvent)
    {
        $data = request()->all();
        $instance = Nayra::startProcess(('bpmn/' . $process . '.bpmn'), $startEvent, $data);
        return $this->encodeResponse($instance);
    }

    public function callProcess($process)
    {
        $data = request()->all();
        $instance = Nayra::callProcess(('bpmn/' . $process . '.bpmn'), $data);
        return $this->encodeResponse($instance);
    }

    public function openStart($process, $requestId, $startEvent)
    {
        $data = request()->all();
        $instance = Nayra::getInstanceById($requestId);
        if (!$instance) {
            $instance = Nayra::startProcess(('bpmn/' . $process . '.bpmn'), $startEvent, $data);
        }
        // Marcar como leido todas las notificaciones del request actual enviados al usuario actual
        Notificacion::where('request_id', $requestId)
            ->where('usuario_id', Auth::id())
            ->update(['leido' => true]);

        return $this->encodeResponse($instance);
    }

    public function open($requestId)
    {
        $instance = Nayra::getInstanceById($requestId);
        if (!$instance) {
            return response()->json(['error' => 'Instance not found'], 404);
        }
        // Marcar como leido todas las notificaciones del request actual enviados al usuario actual
        Notificacion::where('request_id', $requestId)
            ->where('usuario_id', Auth::id())
            ->update(['leido' => true]);

        return $this->encodeResponse($instance);
    }

    public function route($requestId, Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $instance = Nayra::getInstanceById($requestId);
        foreach($instance->getTokens() as $token) {
            $element = $token->getOwnerElement();
            if ($element->getProperty('implementation') === $from) {
                $boundary = $element->getBoundaryEvents();
                foreach ($boundary as $event) {
                    foreach ($event->getOutgoingFlows() as $flow) {
                        if ($flow->getTarget()->getProperty('implementation') === $to) {
                            foreach ($event->getEventDefinitions() as $eventDefinition) {
                                if ($eventDefinition->getProperty('implementation') === 'route') {
                                    $instance = Nayra::executeEvent($requestId, $token->getId(), $eventDefinition->getPayload()->getId());
                                    break 4;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $this->encodeResponse($instance);
    }

    public function message($requestId, $tokenId, $messageRef)
    {
        $instance = Nayra::executeEvent($requestId, $tokenId, $messageRef);
        return $this->encodeResponse($instance);
    }

    public function signal($requestId, $signalRef)
    {
        $instance = Nayra::executeEvent($requestId, null, $signalRef);
        return $this->encodeResponse($instance);
    }

    public function complete($requestId, $tokenId)
    {
        app('events')->listen('ActivityActivated', function (ActivityActivatedEvent $event) use ($requestId) {
            $instance = $event->token->getInstance();
            $instanceId = $instance->getId();
            $proyecto = $instance->getDataStore()->getData('proyecto');
            $proyectoId = is_array($proyecto) ? ($proyecto['id'] ?? null) : null;
            if ($event->activity->getId() === '_3') {
                // Revisión de documentos
                $implementation = $event->activity->getProperty('implementation');
                $token = $event->token->getId();
                Notificacion::create([
                    'usuario_id' => $event->token->getProperty('user'),
                    'icono' => 'fas fa-file',
                    'texto' => 'Tiene un documento para revisar en el proyecto #' . $instanceId,
                    'enlace' => "#/Auditoria/{$implementation}?instance={$instanceId}&token={$token}",
                    'proyecto_id' => $proyectoId,
                    'request_id' => $requestId,
                ]);
            }
            if ($event->activity->getId() === '_24') {
                // Corrección de observaciones
                $implementation = $event->activity->getProperty('implementation');
                $token = $event->token->getId();
                Notificacion::create([
                    'usuario_id' => $event->token->getProperty('user'),
                    'icono' => 'fas fa-file',
                    'texto' => 'Tiene observaciones para revisar en el proyecto #' . $instanceId,
                    'enlace' => "#/Auditoria/{$implementation}?instance={$instanceId}&token={$token}",
                    'proyecto_id' => $proyectoId,
                    'request_id' => $requestId,
                ]);
            }
            if ($event->activity->getId() === '_45') {
                // Auditor en ejecucion
                $implementation = $event->activity->getProperty('implementation');
                $token = $event->token->getId();
                $users = $event->token->getProperty('user');
                if (is_array($users)) {
                    foreach($users as $user) {
                        Notificacion::create([
                            'usuario_id' => $user,
                            'icono' => 'fas fa-file',
                            'texto' => 'Tiene asignado un nuevo proyecto #' . $instanceId,
                            'enlace' => "#/Auditoria/{$implementation}?instance={$instanceId}&token={$token}",
                            'proyecto_id' => $proyectoId,
                            'request_id' => $requestId,
                        ]);
                    }
                } else {
                    Notificacion::create([
                        'usuario_id' => $users,
                        'icono' => 'fas fa-file',
                        'texto' => 'Tiene asignado un nuevo proyecto #' . $instanceId,
                        'enlace' => "#/Auditoria/{$implementation}?instance={$instanceId}&token={$token}",
                        'proyecto_id' => $proyectoId,
                        'request_id' => $requestId,
                    ]);
                }
            }
            if ($event->activity->getId() === '_12') {
                // Cambiar estado a en planificacion
                $proyecto['estado_id'] = Proyecto::ESTADO_EN_PLANIFICACION;
                $instance->getDataStore()->putData('proyecto', $proyecto);
                Proyecto::find($proyectoId)->update([
                    'estado_id' => Proyecto::ESTADO_EN_PLANIFICACION,
                ]);
            }
            if ($event->activity->getId() === '_45') {
                // Cambiar estado a en ejecucion
                $proyecto['estado_id'] = Proyecto::ESTADO_EN_EJECUCION;
                $instance->getDataStore()->putData('proyecto', $proyecto);
                Proyecto::find($proyectoId)->update([
                    'estado_id' => Proyecto::ESTADO_EN_EJECUCION,
                ]);
            }
            if ($event->activity->getId() === '_13') {
                // Cargar documentos firmados
                $implementation = $event->activity->getProperty('implementation');
                $token = $event->token->getId();
                Notificacion::create([
                    'usuario_id' => $event->token->getProperty('user'),
                    'icono' => 'fas fa-file',
                    'texto' => 'Carga los documentos firmados del proyecto #' . $instanceId,
                    'enlace' => "#/Auditoria/{$implementation}?instance={$instanceId}&token={$token}",
                    'proyecto_id' => $proyectoId,
                    'request_id' => $requestId,
                ]);
            }
            if ($event->activity->getId() === '_16') {
                // Revision de requerimiento
                $implementation = $event->activity->getProperty('implementation');
                $token = $event->token->getId();
                Notificacion::create([
                    'usuario_id' => $event->token->getProperty('user'),
                    'icono' => 'fas fa-file',
                    'texto' => 'Tiene un documento para revisar del proyecto #' . $instanceId,
                    'enlace' => "#/Auditoria/{$implementation}?instance={$instanceId}&token={$token}",
                    'proyecto_id' => $proyectoId,
                    'request_id' => $requestId,
                ]);
            }
            if ($event->activity->getId() === '_41') {
                // Correccion requerimiento de informacion
                $implementation = $event->activity->getProperty('implementation');
                $token = $event->token->getId();
                Notificacion::create([
                    'usuario_id' => $event->token->getProperty('user'),
                    'icono' => 'fas fa-file',
                    'texto' => 'Tiene observaciones para revisar del proyecto #' . $instanceId,
                    'enlace' => "#/Auditoria/{$implementation}?instance={$instanceId}&token={$token}",
                    'proyecto_id' => $proyectoId,
                    'request_id' => $requestId,
                ]);
            }
            if ($event->activity->getId() === '_38') {
                // Asignar auditores
                $implementation = $event->activity->getProperty('implementation');
                $token = $event->token->getId();
                Notificacion::create([
                    'usuario_id' => $event->token->getProperty('user'),
                    'icono' => 'fas fa-file',
                    'texto' => 'Debes continuar con la asignación de auditores en el proyecto #' . $instanceId,
                    'enlace' => "#/Auditoria/{$implementation}?instance={$instanceId}&token={$token}",
                    'proyecto_id' => $proyectoId,
                    'request_id' => $requestId,
                ]);
            }
        });
        $data = request()->all();
        $instance = Nayra::getInstanceById($requestId);
        $token = $instance->getTokens()->findFirst(function ($token) use ($tokenId) {
            return $token->getId() === $tokenId;
        });
        $instance = Nayra::completeTask($requestId, $tokenId, $data);
        // almacena el estado de la instancia en registros
        Registro::create([
            'proceso_id' => $requestId,
            'nombre' => $token->getOwnerElement()->getName(),
            'pantalla' => $token->getOwnerElement()->getProperty('implementation'),
            'datos' => $instance->getDataStore()->getData(),
        ]);
        return $this->encodeResponse($instance);
    }

    public function update($requestId, $tokenId)
    {
        $data = request()->all();
        $instance = Nayra::updateTask($requestId, $tokenId, $data);
        return $this->encodeResponse($instance);
    }

    public function history($requestId, Request $request)
    {
        $query = Registro::proceso($requestId)->withAccess(Auth::id());
        return $this->indexFromQuery($query, $request);
    }

    public function openRecord($recordId)
    {
        $registro = Registro::find($recordId);
        // $instance = Nayra::getInstanceById($registro->proceso_id);
        $data = $registro->datos;
        // $tokens = InstanceRepository::dumpTokens($instance->getTokens());
        $tokens = [];
        // $home = Nayra::getPerformerByTypeName($instance->getProcess(), 'performer', 'home', $data);
        $home = $registro->pantalla;
        return response()->json([
            'record' => $registro->getKey(),
            'instance' => $registro->proceso_id,
            'home' => $home,
            'tokens' => $tokens,
            'data' => (object) $data,
        ]);
    }
}
