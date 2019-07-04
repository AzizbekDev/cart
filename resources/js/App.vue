<template>
  <div class="container">
    <navbar></navbar>
    <router-view />
  </div>
</template>
 <script>
import Navbar from "./components/Navbar";

export default {
  components: { Navbar }
};
</script>
 