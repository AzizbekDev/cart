<template>
  <select @change="changed" class="form-control">
    <option value>Please select country</option>
    <option :value="country.id" v-for="country in countries" :key="country.id">{{ country.name }}</option>
  </select>
</template>
<script>
export default {
  data() {
    return {
      countries: []
    };
  },
  methods: {
    async getCountries() {
      let response = await axios.get("api/countries");
      this.countries = response.data.data;
    },
    changed($event) {
      this.$emit("input", $event.target.value);
    }
  },
  created() {
    this.getCountries();
  }
};
</script>