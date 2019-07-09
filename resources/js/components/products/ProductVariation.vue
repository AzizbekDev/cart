<template>
  <div class="form-group">
    <label>{{ type }}</label>
    <select class="form-control" :value="selecedVariation" @change="changed($event, type)">
      <option value>Pleace choose</option>
      <option
        v-for="variation in variations"
        :key="variation.id"
        :value="variation.id"
        :disabled="!variation.in_stock"
      >
        {{ variation.name }}
        <template v-if="variation.price_varies">({{ variation.price }})</template>
        <template v-if="!variation.in_stock">(out of stock)</template>
      </option>
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
    },
    value: {
      requeired: false,
      default: ""
    }
  },
  computed: {
    selecedVariation() {
      if (!this.findVariation(this.value.id)) {
        return "";
      }
      return this.value.id;
    }
  },
  methods: {
    changed($event, type) {
      this.$emit("input", this.findVariation(event.target.value));
    },
    findVariation(id) {
      let variation = this.variations.find(v => v.id == id);
      if (typeof variation == "undefined") {
        return null;
      }
      return variation;
    }
  }
};
</script>

