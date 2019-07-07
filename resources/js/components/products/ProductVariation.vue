<template>
  <div class="form-group">
    <label>{{ type }}</label>
    <select class="form-control">
      <option>Pleace choose</option>
      <option v-for="variation in variations" :key="variation.id">{{ variation.name }}</option>
    </select>
  </div>
</template>
<script>
export default {
  props: {
    type: {
      requeired: true,
      type: String
    },
    variations: {
      requeired: true,
      type: Array
    }
  }
};
</script>

