<template>
  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-md-4 col-sm-12">
          <img src="/images/1.jpg" class="card-img-top" alt="product name" />
        </div>
        <div class="col">
          <div class="card-detail">
            <div class="card-title" v-if="product.name">
              <h3>{{ product.name }}</h3>
            </div>
            <div class="card-price" v-if="product.price">
              <b>Price:</b>
              {{ product.price }}
            </div>
            <div class="card-text" v-if="product.description">
              <p>{{ product.description}}.</p>
            </div>
            <span class="badge badge-warning" v-if="!product.in_stock">Out of stock</span>
          </div>
        </div>
      </div>
    </div>
    <div class="card-footer" v-show="product.variations">
      <form>
        <ProductVariation
          v-for="(variations, type) in product.variations"
          :type="type"
          :key="type"
          :variations="variations"
          v-model="form.variation"
        />

        <div class="input-group mt-5" v-if="form.variation">
          <select class="custom-select" aria-label="quantity">
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
          </select>
          <div class="input-group-append">
            <button class="btn btn-outline-primary" type="button">Add to cart</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
<script>
import ProductVariation from "../../components/products/ProductVariation";
export default {
  data() {
    return {
      product: [],
      form: {
        variation: "",
        quantity: 1
      }
    };
  },
  components: {
    ProductVariation
  },
  mounted() {
    let uri = `/api/products/${this.$route.params.slug}`;
    axios.get(uri).then(response => {
      this.product = response.data.data;
    });
  }
};
</script>
<style scoped>
.card {
  padding: 20px;
}
.card-detail {
  padding: 10px;
}
.card-title {
  padding-bottom: 10px;
  border-bottom: 1px solid gainsboro;
}
</style>

