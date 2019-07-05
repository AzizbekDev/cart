<template>
  <div class="container">
    <Navbar></Navbar>
    <div class="content">
      <router-view></router-view>
    </div>
    <!-- <Footer></Footer> -->
  </div>
</template>
 <script>
import Navbar from "./components/Navbar";
import Footer from "./components/Footer";

export default {
  components: {
    Navbar,
    Footer
  }
};
</script>
<style scoped>
.content {
  max-height: 900px;
  height: 100%;
}
</style>

 