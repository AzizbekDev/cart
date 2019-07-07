<template>
  <div class="card">
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
          <button class="btn btn-primary">Add to cart</button>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  data() {
    return {
      product: []
    };
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
.card-detail button {
  margin-top: 10px;
}
.card-title {
  padding-bottom: 10px;
  border-bottom: 1px solid gainsboro;
}
</style>

