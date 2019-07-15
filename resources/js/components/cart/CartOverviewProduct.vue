<template>
  <tr>
    <td width="150">
      <img src="/images/placeholder120.png" alt="img" />
    </td>
    <td>{{ product.products.name }} / {{ product.name }}</td>
    <td width="120">
      <select class="custom-select">
        <option selected value="0">0</option>
        <option
          :value="x"
          v-for="x in product.stock_count"
          :key="x"
          :selected="x == product.quantity"
        >{{ x }}</option>
      </select>
    </td>
    <td>{{product.total}}</td>
    <td>
      <a href class="btn btn-danger btn-sm" @click.prevent="productDestroy(product.id)">x</a>
    </td>
  </tr>
</template>
<script>
import { mapActions } from "vuex";
export default {
  props: {
    product: {
      required: true,
      type: Object
    }
  },
  methods: {
    ...mapActions({
      destroy: "destroyCart"
    }),
    productDestroy(productId) {
      if (confirm("Are you sure?")) {
        this.destroy(productId);
      }
    }
  }
};
</script>
