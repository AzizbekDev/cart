<template>
  <section id="order-wrapper">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <h3 class="text-muted py-2">Your order</h3>
          <article class="py-3 px-3" v-if="orders.length">
            <table class="table table-hover">
              <tbody>
                <Order v-for="order in orders" :key="order.id" :order="order" />
              </tbody>
            </table>
          </article>
          <p v-else>You haven't ordered yet.</p>
        </div>
      </div>
    </div>
  </section>
</template>
<script>
import Order from "../../components/orders/Order";
export default {
  data() {
    return {
      orders: []
    };
  },
  middleware: [
   'auth'
  ],
  components: {
    Order
  },
  mounted() {
    axios.get("api/orders").then(response => {
      this.orders = response.data.data;
    });
  }
};
</script>
<style scoped>
article {
  background-color: #cccccc0f;
  border-left: 1px solid gray;
}
</style>