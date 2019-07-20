# Lessons from 72 to 81

- [72-Country dropdown selector](#section-1)
- [73-Creating Shipping methods](#section-2)
- [74-Hooking up shipping methods to countries](#section-3)
- [75-Getting the right shipping methods for an address](#section-4)
- [76-Using v-model with a shipping methods for an address](#section-5)
- [77-Outputting available shipping methods](#section-6)
- [78-Adding shipping onto the subtotal](#section-7)
- [79-Displaying shipping price and total at checkout](#section-8)
- [80-Fixing shipping error on checkout](#section-9)
- [81-Adding address and shipping method relation to orders](#section-10)

<a name="section-1"></a>

## Lesson-72 Country dropdown selector

`Create new file resources/js/components/form/CountryDropdown.vue`

```vue
<template>
    <select @change="changed">
        <option value="">Please select country</option>
        <option :value="country.id" v-for="country in countries" :key="country.id">
            {{ country.name }}
        </option>
    </select>
</template>
<script>
export default{
    data(){
        return{
            countries: []
        }
    },
    methods:{
        async getCountries(){
            let response = await axios.get('api/countries');
            this.countries = response.data.data;
        },
        changed($event){
            this.$emit('input', $event.target.value)
        }
    },
    created(){
        this.getCountries();
    }
}
</script>
```

`Edit ../components/checkout/addresses/ShippingAddressCreator.vue`

```vue\
<template>
  ...
    <div class="form-group">
      <label class="lable">Country</label>
      <CountryDropdown v-model="form.country_id"/>
    </div>
  ...
</template>
<script>
import CountryDropdown from "../addresses/CountryDropdown"; // import component
export default {
  data() {
    return {
      form: {
        name: "",
        address_1: "",
        city: "",
        postal_code: "",
        country_id: "",
        default: true
      }
    };
  },
  components: {
    CountryDropdown // than initialize component
  },
  methods: {
    async store() {
      let response = await axios.post("api/addresses", this.form);
      this.$emit("store", response.data.data);
    }
  }
};
</script>
```
<a name="section-2"></a>

## Lesson-73 Country dropdown selector
