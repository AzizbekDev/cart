<template>
  <form action="#" @submit.prevent="store">
    <div class="form-group">
      <label class="lable">Name</label>
      <input type="text" class="form-control" v-model="form.name" />
    </div>
    <div class="form-group">
      <label class="lable">Address line 1</label>
      <input type="text" class="form-control" v-model="form.address_1" />
    </div>
    <div class="form-group">
      <label class="lable">City</label>
      <input type="text" class="form-control" v-model="form.city" />
    </div>
    <div class="form-group">
      <label class="lable">Postal code</label>
      <input type="text" class="form-control" v-model="form.postal_code" />
    </div>
    <div class="form-group">
      <label class="lable">Country</label>
      <select class="form-control" v-model="form.country_id">
        <option value="1">Uzbekistan</option>
      </select>
    </div>
    <div class="form-group">
      <button class="btn btn-primary">Add address</button>
      <button class="btn btn-default" @click.prevent="$emit('cancel')">Cencel</button>
    </div>
  </form>
</template>
<script>
export default {
  data() {
    return {
      form: {
        name: "",
        address_1: "",
        city: "",
        postal_code: "",
        country_id: "",
        default: true
      }
    };
  },
  methods: {
    async store() {
      let response = await axios.post("api/addresses", this.form);
      this.$emit("store", response.data.data);
    }
  }
};
</script>
