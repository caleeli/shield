<template>
  <div class="workspace">
    <span class="nav-container">
      <div class="text-center">
        <b-button
          size="sm"
          variant="link"
          @click="prevPage"
          :disabled="page <= 1"
        >
          &lt;
        </b-button>
        <b-button
          size="sm"
          variant="link"
          @click="nextPage"
          :disabled="page >= numPages"
        >
          &gt;
        </b-button>
        <div style="display: inline-block; float: right;">
          <b-button-group>
            <slot name="toolbar" />
          </b-button-group>
        </div>
      </div>
      <span class="canvas-container">
        <canvas ref="pdfCanvas" class="paper"  @mousemove="mousemove" @mouseup="mouseup"></canvas>
        <slot name="canvas" :numPages="numPages" :page="page" />
      </span>
    </span>
  </div>
</template>

<script>
// Importa de pdfjs-dist
import * as pdfjs from "pdfjs-dist";
const getDocument = pdfjs.getDocument;
pdfjs.GlobalWorkerOptions.workerSrc = "/pdf/pdf.worker.js"; // Ajusta la ruta según tu configuración

export default {
  props: {
    src: {
      type: String,
      required: true,
    },
    page: {
      type: Number,
      default: 1,
    },
  },
  data() {
    return {
      pdf: null,
      numPages: 0,
    };
  },
  watch: {
    src: {
      immediate: true,
      handler(newValue) {
        this.loadPdf(newValue);
      },
    },
  },
  methods: {
    mousemove(e) {
      this.$emit("mousemove", e);
    },
    mouseup(e) {
      this.$emit("mouseup", e);
    },
    async loadPdf(src) {
      try {
        const loadingTask = getDocument(src); // Usa la función getDocument importada
        this.pdf = await loadingTask.promise;
        this.numPages = this.pdf.numPages;
        this.renderPage(Math.min(this.page, this.numPages));
      } catch (error) {
        console.error("Error loading PDF: ", error);
      }
    },
    async renderPage(num) {
      try {
        const page = await this.pdf.getPage(num);
        const viewport = page.getViewport({ scale: 1.0 });
        const canvas = this.$refs.pdfCanvas;
        const context = canvas.getContext("2d");
        canvas.width = viewport.width;
        canvas.height = viewport.height;

        const renderContext = {
          canvasContext: context,
          viewport: viewport,
        };
        await page.render(renderContext);
      } catch (error) {
        console.error("Error rendering page: ", error);
      }
    },
    nextPage() {
      if (this.page < this.numPages) {
        this.renderPage(this.page + 1);
        this.$emit("changepage", this.page + 1);
      }
    },
    prevPage() {
      if (this.page > 1) {
        this.renderPage(this.page - 1);
        this.$emit("changepage", this.page - 1);
      }
    },
  },
};
</script>

<style scoped>
.paper {
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
}
.nav-container {
  display: inline-block;
}
.workspace {
  text-align: center;
}
.canvas-container {
  display: inline-block;
  position: relative;
  margin-top: 4px;
}
</style>
