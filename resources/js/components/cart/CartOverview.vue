<template>
  <table class="table table-hover">
    <tbody>
      <CartOverViewPorduct v-for="product in products" :key="product.id" :product="product" />
    </tbody>
    <tr>
      <td></td>
      <td></td>
      <td class="font-weight-bold">Subtotal</td>
      <td>{{ subtotal }}</td>
      <td></td>
      <td></td>
    </tr>
  </table>
</template>
<script>
import { mapGetters } from "vuex";
import CartOverViewPorduct from "../cart/CartOverviewProduct";
export default {
  computed: {
    ...mapGetters({
      products: "cartProducts",
      subtotal: "cartSubtotal"
    })
  },
  components: {
    CartOverViewPorduct
  }
};
</script>
