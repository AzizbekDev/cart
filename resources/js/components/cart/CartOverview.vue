<template>
  <table class="table table-hover">
    <tbody>
      <CartOverViewPorduct v-for="product in products" :key="product.id" :product="product" />
    </tbody>
  </table>
</template>
<script>
import { mapGetters } from "vuex";
import CartOverViewPorduct from "../cart/CartOverviewProduct";
export default {
  computed: {
    ...mapGetters({
      products: "cartProducts"
    })
  },
  components: {
    CartOverViewPorduct
  }
};
</script>
