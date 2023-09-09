<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use StdClass;
use Illuminate\Http\UploadedFile;

class UploadFileController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @OA\Post(
     *     path="/upload_file",
     *     summary="Upload a file",
     *     operationId="upload_file",
     *     tags={"files"},
     *     security={
     *         {"passport": {}}
     *     },
     *     @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *             @OA\Property(
     *                property="file",
     *                description="upload a new file",
     *                type="string",
     *                format="binary",
     *              ),
     *            ),
     *        ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="mime", type="string"),
     *             @OA\Property(property="path", type="string"),
     *             @OA\Property(property="url", type="string"),
     *         ),
     *     ),
     *     @OA\Response(response=403, ref="#/components/responses/403"),
     *     @OA\Response(response=404, ref="#/components/responses/404"),
     * )
     */
    public function upload(Request $request)
    {
        
        $files = $request->file('file');
        $multiple = is_array($files);
        $response = $multiple ? [] : $this->packResponse($files);
        if ($multiple) {
            foreach ($files as $file) {
                $response[] = $this->packResponse($file);
            }
        }
        return response()->json($response);
    }

    private function packResponse(UploadedFile $file)
    {
        $json = new StdClass();
        $json->name = $file->getClientOriginalName();
        // Abort disallowed extensions
        $disallowedExtensions = config('filesystems.disallowed-extensions', []);
        if (in_array($file->getClientOriginalExtension(), $disallowedExtensions)) {
            abort(403, __('Disallowed file extension'));
        }
        $json->mime = $file->getClientMimeType();
        $json->path = $file->storePubliclyAs('', $this->getPublicName($file), 'public');
        $json->url = asset('storage/' . $json->path);
        $json->size = $file->getSize();
        $json->date = date('Y-m-d', $file->getCTime());
        $json->dateTime = date('Y-m-d H:s', $file->getCTime());
        return $json;
    }

    private function getPublicName(UploadedFile $file)
    {
        return md5(uniqid('', true)) . '/' . $file->getClientOriginalName();
    }
}
