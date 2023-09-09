// import workflow store

class Workflow {
    constructor(vueInstance) {
        this.vue = vueInstance;
    }

    openCreate(payload) {
        // validate payload has { instanceId, processId, startEvent, data }
        if (!payload.instanceId) {
            throw new Error("instanceId is required");
        }
        if (!payload.processId) {
            throw new Error("processId is required");
        }
        if (!payload.startEvent) {
            throw new Error("startEvent is required");
        }
        this.vue.$store.dispatch('workflow/openStartInstance', payload);
    }
}

export default Workflow;
