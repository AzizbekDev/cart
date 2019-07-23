<template>
  <section id="checkout-container">
    <div class="container">
      <div class="row py-5">
        <div class="col-md-8 order-md-1">
          <form class="needs-validation" novalidate>
            <ShippingAddress :addresses="address" v-model="form.address_id"></ShippingAddress>
            <article class="pl-4" v-if="shippingMethodId">
              <h3 class="text-muted pt-2 mb-3">Shipping</h3>
              <div class="form-group">
                <select class="form-control" v-model="shippingMethodId">
                  <option
                    v-for="shipping in shippingMethods"
                    :key="shipping.id"
                    :value="shipping.id"
                  >{{ shipping.name }} ({{ shipping.price }})</option>
                </select>
              </div>
            </article>
            <div class="cart-overview" v-if="products.length">
              <h3 class="text-muted pt-2 mb-3">Your cart</h3>
              <article class="pl-4">
                <CartOverView>
                  <template slot="rows">
                    <tr v-if="shippingMethodId">
                      <td></td>
                      <td></td>
                      <td class="font-weight-bold">Shipping</td>
                      <td>{{ shipping.price }}</td>
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
              </article>
            </div>
            <button
              class="btn btn-primary btn-sm btn-block"
              type="submit"
              :disabled="empty || submitting"
              @click.prevent="order"
            >
              <i class="fa fa-credit-card"></i> Continue to checkout
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>
</template>
<script>
import { mapGetters, mapActions } from "vuex";
import ShippingAddress from "../../components/checkout/addresses/ShippingAddress";
import CartOverView from "../../components/cart/CartOverview";
export default {
  data() {
    return {
      submitting: false,
      address: [],
      shippingMethods: [],
      form: {
        address_id: null
      }
    };
  },
  watch: {
    "form.address_id"(addressId) {
      this.getShippingMethodsForAddress(addressId).then(() => {
        this.setShipping(this.shippingMethods[0]);
      });
    },
    shippingMethodId() {
      this.getCart();
    }
  },
  components: {
    CartOverView,
    ShippingAddress
  },
  computed: {
    ...mapGetters({
      total: "cartTotal",
      products: "cartProducts",
      empty: "cartEmpty",
      shipping: "shipping"
    }),
    shippingMethodId: {
      get() {
        return this.shipping ? this.shipping.id : "";
      },
      set(shippingMethodId) {
        this.setShipping(
          this.shippingMethods.find(s => s.id === shippingMethodId)
        );
      }
    }
  },
  methods: {
    ...mapActions({
      setShipping: "storeShipping",
      getCart: "getCart"
    }),
    async getAddresses() {
      const auth = {
        headers: { Authorization: "Bearer " + localStorage.getItem("token") }
      };
      let response = await axios.get("api/addresses", auth);
      this.address = response.data.data;
    },
    async order() {
      this.submitting = true;
      try {
        axios.post("api/orders", {
          ...this.form,
          shipping_method_id: this.shippingMethodId
        });
        await this.getCart();
        this.$router.replace({
          name: "orders"
        });
      } catch (e) {
        //
      }
    },
    async getShippingMethodsForAddress(addressId) {
      let response = await axios.get(`api/addresses/${addressId}/shipping`);
      this.shippingMethods = response.data.data;
      return response;
    }
  },
  created() {
    this.getAddresses();
  }
};
</script>
<style scoped>
article {
  border-left: 1px solid gray;
}
</style>

