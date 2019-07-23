<template>
  <div
    class="alert alert-info alert-dismissible fade show"
    role="alert"
    v-if="alert"
    @click.prevent="clearMessage"
  >
    {{ alert }}.
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
</template>
<script>
import { mapGetters, mapActions } from "vuex";
export default {
  computed: {
    ...mapGetters({
      alert: "alertMessage"
    })
  },
  methods: {
    ...mapActions({
      clearMessage: "clearMessage"
    })
  }
};
</script>
