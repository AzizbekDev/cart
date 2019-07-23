<template>
  <div class="container">
    <Navbar></Navbar>
    <Alert></Alert>
    <div class="content">
      <router-view></router-view>
    </div>
    <!-- <Footer></Footer> -->
  </div>
</template>
 <script>
import Navbar from "./components/Navbar";
import Alert from "./components/globals/TheAlert";
import Footer from "./components/Footer";

export default {
  components: {
    Navbar,
    Alert,
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

 