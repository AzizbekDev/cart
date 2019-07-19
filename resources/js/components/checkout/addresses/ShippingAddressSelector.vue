<template>
  <table class="table table-hover">
    <tbody>
      <tr
        v-for="address in addresses"
        :key="address.id"
        :class="{'table-success': address.id == selectedAddress.id }"
      >
        <td>
          <p>
            {{ address.name }}
            <br />
            {{ address.address_1 }}
            <br />
            {{ address.city }}
            <br />
            {{ address.postal_code }}
            <br />
            {{ address.country.name }}
          </p>
        </td>
        <td>
          <a
            href
            class="btn btn-primary btn-sm"
            role="button"
            @click.prevent="$emit('click', address)"
          >Ship here</a>
        </td>
      </tr>
    </tbody>
  </table>
</template>
<script>
export default {
  props: {
    addresses: {
      required: true,
      type: Array
    },
    selectedAddress: {
      required: true,
      type: Object
    }
  },
  data() {
    return {
      selectedAddressid: 1
    };
  }
};
</script>
