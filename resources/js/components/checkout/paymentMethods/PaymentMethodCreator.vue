<template>
  <form action="#" @submit.prevent="store">
    <div class="form-group">
      <label for="card-element">Credit or debit card</label>
      <div id="card-element" class="form-control"></div>
    </div>
    <div class="form-group">
      <button type="submit" class="btn btn-primary btn-sm" :disabled="storing">Store card</button>
      <button type="button" class="btn btn-light btn-sm" @click.prevent="$emit('cancel')">Cancel</button>
    </div>
  </form>
</template>
<script>
export default {
  data() {
    return {
      stripe: null,
      card: null,
      storing: false
    };
  },
  methods: {
    async store() {
      this.storing = true;
      const { token, error } = await this.stripe.createToken(this.card);
      if (error) {
        //
      } else {
        let response = await axios.post("api/payment-methods", {
          token: token.id
        });
        this.$emit("added", response.data);
      }
      this.storing = false;
    }
  },
  mounted() {
    const stripe = Stripe("pk_test_EzuIlgdPvUDCa2ScLlsdo06j00qtkWwqZe");
    this.stripe = stripe;
    this.card = stripe.elements().create("card");
    this.card.mount("#card-element");
  }
};
</script>
