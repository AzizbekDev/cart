<template>
  <div class="card">
    <img src="/images/1.jpg" class="card-img-top" alt="Product image" />
    <div class="card-body">
      <h5 class="card-title">{{ product.name }}</h5>
      <p class="card-text">
        <b>Price:</b>
        {{ product.price }}$
      </p>
      <center>
        <router-link
          :to="{name: 'products-slug',params:{slug: product.slug}}"
          class="btn btn-primary"
          exact
        >Add card</router-link>
        <router-link
          :to="{name: 'products-slug',params:{slug: product.slug}}"
          class="btn btn-success"
          exact
        >More detail</router-link>
      </center>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    product: {
      required: true,
      type: Object
    }
  }
};
</script>
