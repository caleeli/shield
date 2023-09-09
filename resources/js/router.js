import routes from "./pages";
import VueRouter from "vue-router";
import store from "./store";

const router = new VueRouter({
  mode: "hash",
  routes,
});

// on before
router.beforeEach(async (to, from, next) => {
  next();
  const componentTo = to;
  if (to.query.instance) {
    await store.dispatch("workflow/openInstance", to.query.instance);
  } else if (to.query.record) {
    await store.dispatch("workflow/openRecord", to.query.record);
  }
});

export default router;
