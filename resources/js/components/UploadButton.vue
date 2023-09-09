<template>
  <div class="upload-region" @click="click">
    <b-button v-bind="$attrs" v-on="$listeners">
      <slot />
    </b-button>
    <div class="form-file-progress">
      <input
        type="file"
        v-on:change="changeFile($event, multiplefile)"
        v-bind:accept="accept"
        v-bind:multiple="multiplefile"
        v-bind="$attrs"
      />
    </div>
  </div>
</template>

<script>
export default {
  props: {
    value: null,
    accept: {
      type: String,
      default: "",
    },
    multiplefile: Boolean,
    url: {
      type: String,
      default: "/api/upload_file",
    },
  },
  computed: {
    bgStyle() {
      return `linear-gradient(90deg, rgba(0,0,0,0.5) ${
        this.progress
      }%, transparent ${100 - this.progress}%)`;
    },
  },
  data() {
    return {
      progress: 100,
      name: "",
    };
  },
  methods: {
    click(event) {
      this.$emit("click", event);
    },
    changeFile: function (event, multiple) {
      var self = this;
      var data = new FormData();
      for (var i = 0, l = event.target.files.length; i < l; i++) {
        data.append("file" + (multiple ? "[]" : ""), event.target.files[i]);
      }
      this.progress = 0;
      const url =
        this.url +
        (this.accept
          ? (this.url.indexOf("?") === -1 ? "?" : "&") +
            "accept=" +
            encodeURIComponent(this.accept)
          : "");

      // meta[name="csrf-token"]
      const token = document.head.querySelector('meta[name="csrf-token"]').content;
      fetch(url, {
        method: "POST",
        body: data,
        headers: {
          Accept: "application/json",
          'X-CSRF-TOKEN': token,
        },
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error("Network response was not ok");
          }
          return response.json();
        })
        .then((json) => {
          this.name = json.name;
          this.$emit("input", json);
          this.$emit("change", json);
          this.progress = 100;
        })
        .catch((error) => {
          this.progress = 100;
          this.$emit("error", error);
        });
      // clear input file value
      event.target.value = "";
    },
  },
  watch: {
    value() {
      this.name = this.value?.name || "";
    },
  },
};
</script>

<style scoped>
.upload-container {
  width: 100%;
  height: 100%;
}
.upload-region {
  position: relative;
  min-height: 1rem;
  display: inline-block;
}
.form-file-progress {
  width: 100%;
  height: 100%;
  position: absolute;
  left: 0px;
  top: 0px;
}
.form-file-progress input {
  width: 100%;
  height: 100%;
  position: absolute;
  left: 0px;
  top: 0px;
  opacity: 0;
  z-index: 10;
  cursor: pointer;
}
.form-file-progress input::-webkit-file-upload-button {
    cursor: pointer; 
}
</style>
