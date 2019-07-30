<template>
  <tr>
    <th scope="col">#{{ order.id }}</th>
    <th scope="col">{{ order.created_at }}</th>
    <th scope="col">
      <div v-for="variation in products" :key="variation.id">
        <router-link
          :to="{
          name: 'products-slug',
          params:{
            slug: variation.product.slug
          }
        }"
        >{{ variation.product.name }} ({{ variation.name }}) - {{ variation.type }}</router-link>
      </div>
      <template v-if="moreProducts > 0">and {{ moreProducts }} more</template>
    </th>
    <th scope="col">{{ order.subtotal }}</th>
    <th scope="col">
      <component :is="order.status" />
    </th>
  </tr>
</template>
<script>
import OrderStatusPaymentFailed from "./statuses/OrderStatusPaymentFailed";
import OrderStatusProcessing from "./statuses/OrderStatusProcessing";
import OrderStatusPending from "./statuses/OrderStatusPending";
import OrderStatusCompleted from "./statuses/OrderStatusCompleted";

export default {
  components: {
    payment_faild: OrderStatusPaymentFailed,
    processing: OrderStatusProcessing,
    pending: OrderStatusPending,
    completed: OrderStatusCompleted
  },
  data() {
    return {
      maxProducts: 2
    };
  },
  props: {
    order: {
      required: true,
      type: Object
    }
  },
  computed: {
    products() {
      return this.order.products.slice(0, this.maxProducts);
    },
    moreProducts() {
      return this.order.length - this.maxProducts;
    }
  }
};
</script>
<style scoped>
.table th,
.table td {
  border-top: none;
}
</style>
