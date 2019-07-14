<template>
  <div class="section cart-wrapper">
    <div class="container">
      <div class="card">
        <div class="card-header">
          <h4 class="m-auto">Your cart</h4>
        </div>
        <div class="card-body">
          <CartOverview />
        </div>
        <div class="card-footer">
          <button class="btn btn-primary btn-block btn-sm">Checkout</button>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import CartOverview from "../../components/cart/CartOverview";
export default {
  components: {
    CartOverview
  }
};
</script>

