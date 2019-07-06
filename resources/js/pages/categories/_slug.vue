<template>
  <div class="section">
    <div class="container">
      <div v-if="products.length" class="row">
        <div class="col-4" v-for="product in products" :key="product.slug">
          <Product :product="product" />
        </div>
      </div>
      <div v-else>
        <p>No Products</p>
      </div>
    </div>
  </div>
</template>
<script>
import Product from "../../components/products/Product";
export default {
  data() {
    return {
      products: []
    };
  },
  components: {
    Product
  },
  created() {
    this.fetchData();
  },
  watch: {
    $route: "fetchData"
  },
  methods: {
    async fetchData() {
      let uri = `/api/products?category=${this.$route.params.slug}`;
      let response = await axios.get(uri);
      return (this.products = response.data.data);
    }
  }
};
</script>
