<template>
  <article class="pl-4">
    <h3 class="text-muted pt-2 mb-3">Payment methods</h3>
    <template v-if="selecting">
      <PaymentMethodSelector
        :payment-methods="paymentMethods"
        :selected-payment-method="selectedPaymentMethod"
        @click="paymentMethodSelected"
      />
    </template>
    <template v-else-if="creating">Create new Payment method</template>
    <template v-else>
      <template v-if="selectedPaymentMethod">
        <p>{{ selectedPaymentMethod.cart_type }} ending {{ selectedPaymentMethod.last_four }}</p>
        <br />
      </template>
      <template v-else>
        <p>No payment method selected</p>
        <br />
      </template>
      <div class="field">
        <p>
          <button
            type="button"
            @click.prevent="selecting = true"
            class="btn btn-primary btn-sm"
          >Change payment method</button>
          <button
            type="button"
            @click.prevent="creating = true"
            class="btn btn-primary btn-sm"
          >Add a payment method</button>
        </p>
      </div>
    </template>
  </article>
</template>
<script>
import PaymentMethodSelector from "../paymentMethods/PaymentMethodSelector";
export default {
  props: {
    paymentMethods: {
      required: true,
      type: Array
    }
  },
  data() {
    return {
      selecting: false,
      creating: false,
      selectedPaymentMethod: null
    };
  },
  components: {
    PaymentMethodSelector
  },
  watch: {
    defaultPaymentMethod(v) {
      if (v) {
        this.switchPaymentMethod(v);
      }
    },
    selectedPaymentMethod(paymentMethod) {
      this.$emit("input", paymentMethod.id);
    }
  },
  computed: {
    defaultPaymentMethod() {
      return this.paymentMethods.find(a => a.default === 1);
    }
  },
  methods: {
    paymentMethodSelected(paymentMethod) {
      this.switchPaymentMethod(paymentMethod);
      this.selecting = false;
    },
    switchPaymentMethod(paymentMethod) {
      this.selectedPaymentMethod = paymentMethod;
    },
    created(paymentMethod) {
      this.paymentMethodSelected = paymentMethod;
      this.creating = false;
      this.switchPaymentMethod(paymentMethod);
    }
  }
};
</script>
<style scoped>
article {
  border-left: 1px solid gray;
}
.field {
  display: block;
}
</style>
