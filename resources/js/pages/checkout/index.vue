<template>
  <section id="checkout-container">
    <div class="container">
      <div class="row py-5">
        <div class="col-md-8 order-md-1">
          <h4 class="mb-3">Billing address</h4>
          <form class="needs-validation" novalidate>
            <ShippingAddress :addresses="address"></ShippingAddress>
            <hr class="mb-4" />
            <div class="cart-overview border border-info p-2" v-if="products.length">
              <h3 class="text-muted pt-2 mb-2">Your cart</h3>
              <CartOverView>
                <template slot="rows">
                  <tr>
                    <td></td>
                    <td></td>
                    <td class="font-weight-bold">Shipping</td>
                    <td>£0.00</td>
                    <td></td>
                    <td></td>
                  </tr>
                  <tr>
                    <td></td>
                    <td></td>
                    <td class="font-weight-bold">Total</td>
                    <td>{{ total }}</td>
                    <td></td>
                    <td></td>
                  </tr>
                </template>
              </CartOverView>
            </div>
            <hr class="mb-4" />
            <button class="btn btn-primary btn-lg btn-block" type="submit" :disabled="empty">
              <i class="fa fa-credit-card"></i> Continue to checkout
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>
</template>
<script>
import { mapGetters } from "vuex";
import ShippingAddress from "../../components/checkout/addresses/ShippingAddress";
import CartOverView from "../../components/cart/CartOverview";
export default {
  data() {
    return {
      address: []
    };
  },
  components: {
    CartOverView,
    ShippingAddress
  },
  computed: {
    ...mapGetters({
      total: "cartTotal",
      products: "cartProducts",
      empty: "cartEmpty"
    })
  },
  methods: {
    async getAddresses() {
      const auth = {
        headers: { Authorization: "Bearer " + localStorage.getItem("token") }
      };
      let response = await axios.get("api/addresses", auth);
      this.address = response.data.data;
    }
  },
  created() {
    this.getAddresses();
  }
};
</script>
