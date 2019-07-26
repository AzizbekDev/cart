<template>
  <table class="table table-hover">
    <tbody>
      <tr
        v-for="paymentMethod in paymentMethods"
        :key="paymentMethod.id"
        :class="{'table-success': paymentMethod.id == selectedPaymentMethod.id }"
      >
        <td>
          <p>{{ paymentMethod.cart_type }} ending ...{{ paymentMethod.last_four }}</p>
        </td>
        <td>
          <a
            href
            class="btn btn-primary btn-sm"
            role="button"
            @click.prevent="$emit('click', paymentMethod)"
          >Pay with this</a>
        </td>
      </tr>
    </tbody>
  </table>
</template>
<script>
export default {
  props: {
    paymentMethods: {
      required: true,
      type: Array
    },
    selectedPaymentMethod: {
      required: true,
      type: Object
    }
  },
  data() {
    return {
      selectedAddressid: 1
    };
  }
};
</script>
