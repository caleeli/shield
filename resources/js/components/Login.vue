<!--
Una pantalla bootrstrap-vue de ingreso al sistema:
- titulo en h3 centrado
- formulario con 2 inputs (username y password) con estilo ".input-border-bottom"
- Recuerdame con estilo ".custom-checkbox", una enlace "olvidaste tu contraseña" con estilo ".text-muted"
- boton de ingresar redondeado con estilo ".btn-login"
-->
<template>
  <div class="login">
    <div class="container container-login animated fadeIn">
      <h3 class="text-center">Ingreso al Sistema</h3>
      <form @submit.prevent="onSubmit">
        <b-form-group label="Nombre de Usuario" label-for="username">
          <b-form-input
            id="username"
            v-model="username"
            required
            placeholder="Ingresa tu nombre de usuario"
          ></b-form-input>
        </b-form-group>

        <b-form-group label="Contraseña" label-for="password">
          <b-form-input
            id="password"
            type="password"
            v-model="password"
            required
            placeholder="Ingresa tu contraseña"
          ></b-form-input>
        </b-form-group>

        <div class="d-flex">
          <b-form-checkbox
            v-model="remember"
            name="remember"
            class="custom-checkbox"
          >
            Recuérdame
          </b-form-checkbox>

          <div class="text-muted">
            <a href="#" @click="onForgotPassword">¿Olvidaste tu contraseña?</a>
          </div>
        </div>

        <div class="mt-3 text-center">
          <b-button
            type="submit"
            variant="primary"
            class="btn-login"
            pill
            :disabled="isSubmitting"
          >
            Ingresar
          </b-button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      username: "",
      password: "",
      remember: false,
      isSubmitting: false,
      csrfToken: document.querySelector('meta[name="csrf-token"]').content,
    };
  },
  methods: {
    async onSubmit() {
      // Validar los campos
      if (!this.username || !this.password) {
        this.$bvModal.msgBoxOk("Ingresa tu nombre de usuario y contraseña.", {
          title: "Error",
          size: "sm",
          buttonSize: "sm",
          okVariant: "danger",
          okTitle: "OK",
          centered: true,
        });
        return;
      }
      try {
        const response = await fetch("/login", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": this.csrfToken,
          },
          body: JSON.stringify({
            username: this.username,
            password: this.password,
          }),
        });
        if (response.ok) {
          const data = await response.json();
          localStorage.setItem("user", JSON.stringify(data.user));
          window.location.href = "/main#/HelloWorld";
        } else if (response.status === 419) {
          // CSRF token mismatch
          window.location.reload();
        } else {
          const data = await response.json();
          throw new Error(data.message);
        }
      } catch (error) {
        this.$alert(
          "Inicio de sesión fallido",
          error.message || "Error al iniciar sesión. Por favor, intenta de nuevo.",
          { variant: "danger" }
        );
      }
    },
    onForgotPassword() {
      this.$bvModal.msgBoxOk(
        "Por favor contacta al administrador para recuperar tu contraseña."
      );
    },
  },
};
</script>

<style scoped>
.container-login {
  max-width: 440px;
  margin: 3rem auto;
  padding: 60px 25px;
  border-radius: 5px;
  box-shadow: 0 0.75rem 1.5rem rgb(18 38 63 / 3%);
  border: 1px solid #ebecec;
  background: #fff9;
  backdrop-filter: blur(4px);
}
.form-control {
  border-width: 0 0 1px 0;
  border-radius: 0px;
  padding: 0.75rem;
}
.login {
  background-color: #efefee;
  background-image: url("https://storage.googleapis.com/pai-images/upscaled_image-060415fe-8b5a-4734-ba3e-3a773dad82f8.png");
  background-size: cover;
  width: 100vw;
  height: 100vh;
  margin: 0px;
  position: absolute;
  top: 0;
  overflow-y: auto;
}
.btn-login {
  padding: 15px 0;
  width: 135px;
  display: inline-block;
}
.login h3 {
  font-size: 19px;
  font-weight: 600;
  margin-bottom: 25px;
}
</style>
