<template>
  <div class="border border-left-0" v-if="selectedAddress">
    {{ selectedAddress }}
    <br />
  </div>
  <div v-else>no address</div>
</template>
<script>
export default {
  props: {
    addresses: {
      required: true,
      type: Array
    }
  },
  data() {
    return {
      localAddresses: this.addresses,
      selectedAddress: null
    };
  },
  computed: {
    defaultAddress() {
      return this.localAddresses.find(a => a.default === 1);
    }
  },
  methods: {
    switchAddress(address) {
      this.selectedAddress = address;
    }
  },
  created() {
    if (this.addresses.length) {
      this.switchAddress(this.defaultAddress);
    }
  }
};
</script>
