<template>
  <tr>
    <th scope="col">#{{ order.id }}</th>
    <th scope="col">{{ order.created_at }}</th>
    <th scope="col">
      <div v-for="product in products" :key="product.id">
        <a href="#">Product 1</a>
        <a href="#">Product 2</a>
      </div>
      <template v-if="moreProducts > 0">and {{ moreProducts }} more</template>
    </th>
    <th scope="col">{{ order.subtotal }}</th>
    <th scope="col">
      <span :class="statusClass">{{ order.status }}</span>
    </th>
  </tr>
</template>
<script>
export default {
  data() {
    return {
      maxProducts: 2,
      statusClass: {
        "text-danger": this.order.status === "payment_faild",
        "text-info":
          this.order.status === "processing" || this.order.status === "pending",
        "text-success": this.order.status === "complite"
      }
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
