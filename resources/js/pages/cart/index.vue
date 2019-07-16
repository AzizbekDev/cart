<template>
  <div class="section cart-wrapper">
    <div class="container">
      <div class="alert alert-warning alert-dismissible fade show" v-if="changed">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Warning!</strong> The Product has been changed.
      </div>
      <div class="card">
        <div class="card-header">
          <h4 class="m-auto">Your cart</h4>
        </div>
        <div class="card-body">
          <template v-if="products.length">
            <CartOverview />
          </template>
          <template v-else>
            <p>Your cart is empty</p>
          </template>
        </div>
        <div class="card-footer">
          <router-link
            :to="{name: 'checkout'}"
            class="btn btn-primary btn-block btn-sm"
            v-if="!empty"
          >Checkout</router-link>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { mapGetters } from "vuex";
import CartOverview from "../../components/cart/CartOverview";
export default {
  components: {
    CartOverview
  },
  computed: {
    ...mapGetters({
      empty: "cartEmpty",
      products: "cartProducts",
      changed: "cartChanged"
    })
  }
};
</script>

