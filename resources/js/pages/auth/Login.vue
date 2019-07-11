<template>
  <div class="container">
    <div class="row mt-5">
      <div class="col-md-6 m-auto">
        <h3>Sign in</h3>
      </div>
    </div>
    <div class="row">
      <form class="col-md-6 m-auto" @submit.prevent="signin">
        <div class="form-group">
          <label for="email">Email address</label>
          <input
            type="email"
            class="form-control"
            id="email"
            v-model="form.email"
            aria-describedby="emailHelp"
            placeholder="Enter email"
          />
          <small
            id="emailHelp"
            class="form-text text-muted"
          >We'll never share your email with anyone else.</small>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input
            type="password"
            class="form-control"
            id="password"
            v-model="form.password"
            placeholder="Password"
          />
        </div>
        <button type="submit" class="btn btn-primary">Sign in</button>
      </form>
    </div>
  </div>
</template>
<script>
import { login } from "../../helpers/index";
export default {
  data() {
    return {
      error: null,
      form: {
        email: "",
        password: ""
      }
    };
  },
  methods: {
    signin() {
      this.$store.dispatch("login");
      login(this.$data.form)
        .then(res => {
          this.$store.commit("loginSuccess", res);
          this.$router.push({ path: "/" });
        })
        .catch(error => {
          this.$store.commit("loginFailed", { error });
        });
    }
  },
  created() {
    console.log(this.$store.state.isLoggedIn);
  }
};
</script>