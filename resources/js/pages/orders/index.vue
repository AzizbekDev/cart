<template>
  <section id="order-wrapper">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <h3 class="text-muted py-2">Your order</h3>
          <article class="py-3 px-3">
            <table class="table table-hover">
              <tbody>
                <Order />
              </tbody>
            </table>
          </article>
        </div>
      </div>
    </div>
  </section>
</template>
<script>
import Order from "../../components/orders/Order";
export default {
  components: {
    Order
  }
};
</script>
<style scoped>
article {
  background-color: #cccccc0f;
  border-left: 1px solid gray;
}
</style>