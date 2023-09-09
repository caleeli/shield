export default {
  methods: {
    $focusTabIndex(index) {
      let el = document.querySelector(`[tabindex="${index}"]`);
      if (el) {
        el.focus();
      }
    },
    $submit(data) {
      this.$store.dispatch("workflow/completeTask", {
        instanceId: this.$route.query.instance,
        tokenId: this.$route.query.token,
        data,
      });
    },
    $wait(ms) {
      return new Promise((resolve) => {
        setTimeout(resolve, ms);
      });
    },
    $alert(title, message, options = {}) {
      this.$root.$bvToast.toast(message, {
        title,
        autoHideDelay: 5000,
        solid: false,
        appendToast: true,
        variant: "success",
        ...options,
      });
    }
  }
};
