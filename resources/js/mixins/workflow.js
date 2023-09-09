import { mapState } from "vuex";
import get from "lodash/get";
import set from "lodash/set";

export default function ({ bpmn, data, startEvent }) {

  return {
    computed: {
      ...mapState({
        workflowData: (state) => state.workflow.data,
      }),
      // ...computedProperties,
    },
    created() {
      // initialize data
      const defaultValues = {};
      Object.keys(data).forEach((key) => {
        const defaultValue = data[key].default || null;
        if (defaultValue) {
          defaultValues[key] = defaultValue;
        }
      });
      this.$store.dispatch("workflow/initializeData", defaultValues);
      // check if instance is open
      if (!this.$route.query.instance) {
        this.$store.dispatch("workflow/startProcess", {
          processId: bpmn,
          startEvent: startEvent,
          data: defaultValues,
        });
      }
    },
    methods: {
      completeTask() {
        const newData = Object.assign({}, this.workflowData);
        Object.keys(data)
        .filter((key) => !data[key].readonly)
        .forEach((key) => {
          const def = data[key];
          if (def.key) {
            set(newData, def.key, this[key]);
          }
        });
        this.$store.dispatch("workflow/completeTask", {
          instanceId: this.$route.query.instance,
          tokenId: this.$route.query.token,
          data: newData,
        });
      },
      updateTask() {
        const newData = Object.assign({}, this.workflowData);
        Object.keys(data).forEach((key) => {
          const def = data[key];
          if (def.key) {
            set(newData, def.key, this[key]);
          }
        });
        this.$store.dispatch("workflow/updateTask", {
          instanceId: this.$route.query.instance,
          tokenId: this.$route.query.token,
          data: newData,
        });
      },
    },
    watch: {
      workflowData(newData) {
        Object.keys(data).forEach((key) => {
          const def = data[key];
          if (def.key) {
            let value = get(newData, def.key, def.default || null);
            // pre-process value if a transform function is provided
            if (def.transform instanceof Function) {
              value = def.transform.apply(this, [value]);
            }
            this[key] = value;
          }
        });
      },
    },
  };
};
