<template>
  <b-navbar class="navbar" :toggleable="false" type="dark" variant="primary">
    <b-button
      v-if="isSmall"
      variant="link"
      size="sm"
      class="float-right text-white"
      @click="$emit('toggle-sidebar')"
    >
      <i :class="`fas fa-bars`" />
    </b-button>

    <b-popover
      target="notificaciones-bell"
      triggers="blur click"
      placement="bottomleft"
      :show.sync="showNotificaciones"
    >
      <template #title>Notificaciones</template>
      <b-list-group>
        <b-list-group-item
          v-for="notificacion in notificaciones.data"
          :key="notificacion.id"
          :href="notificacion.enlace"
          class="d-flex align-items-center m-0 p-1"
          @click="leido(notificacion.id)"
        >
          <!-- i :class="`${notificacion.icono || 'fas fa-bell'} mr-4`" / -->
          <b-avatar
            :src="notificacion.creado_por.imagen.url"
            :alt="notificacion.creado_por.nombres_apellidos"
            size="2rem"
            class="mr-1"
          ></b-avatar>
          <small>
            <b>{{ notificacion.creado_por.nombres_apellidos }}</b><br />
            {{ notificacion.texto }}
          </small>
        </b-list-group-item>
      </b-list-group>
    </b-popover>

    <b-collapse id="nav-collapse" is-nav>
      <!-- Right aligned nav items -->
      <b-navbar-nav class="ml-auto">
        <!-- notifications -->
        <b-nav-item id="notificaciones-bell" @click="actualizarNotificaciones">
          <i class="fas fa-bell" />
          <span v-if="notificaciones.data.length > 0" class="notification">
            {{ notificaciones.total }}
          </span>
        </b-nav-item>
        <b-nav-item-dropdown right no-caret class="avatar-button">
          <template #button-content>
            <em>{{ user.nombres_apellidos }}</em>
            <img
              :src="user.imagen?.url"
              class="rounded-circle ml-2"
              width="30"
              height="30"
            />
          </template>
          <b-dropdown-item href="#/Configuracion/Perfil">
            <i class="fas fa-user mr-2" />
            Perfil
          </b-dropdown-item>
          <b-dropdown-item href="/">
            <i class="fas fa-sign-out-alt mr-2" />
            Salir
          </b-dropdown-item>
        </b-nav-item-dropdown>
      </b-navbar-nav>
    </b-collapse>
  </b-navbar>
</template>

<script>
import { mapState } from "vuex";

export default {
  data() {
    return {
      showNotificaciones: false,
      isSmall: window.innerWidth < 992,
      notificaciones: { data: [] },
    };
  },
  computed: {
    ...mapState({
      user: (state) => state.user.user,
      workflow: state => state.workflow,
    }),
  },
  methods: {
    selectNotificaciones() {
      if (!this.user) {
        return { data: [] };
      }
      return this.$api.selectObject(
        `notificacion?include=creadoPor&filter[]=whereUsuarioId(${this.user.id})&filter[]=whereLeido(0)&sort=-id&per_page=6`,
        {},
        { data: [], total: 0 }
      );
    },
    actualizarNotificaciones() {
      this.notificaciones = this.selectNotificaciones();
    },
    async leido(id) {
      this.showNotificaciones = false;
      await this.$api.put(`notificacion/${id}`, {
        leido: true,
      });
      this.actualizarNotificaciones();
    },
  },
  mounted() {
    window.addEventListener("resize", () => {
      this.isSmall = window.innerWidth < 992;
    });
    this.actualizarNotificaciones();
    // refresh notificaciones each 1 minutes
    setInterval(() => {
      this.actualizarNotificaciones();
    }, 0.5 * 60 * 1000);
  },
  watch: {
    workflow: {
      handler() {
        this.actualizarNotificaciones();
      },
      deep: true,
    },
  },
};
</script>

<style>
.avatar-button a {
  padding: 5px !important;
}
</style>
