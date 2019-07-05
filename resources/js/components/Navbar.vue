<template>
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <router-link class="navbar-brand" :to="{name: 'home'}" exact>Cart</router-link>
    <button
      class="navbar-toggler"
      type="button"
      data-toggle="collapse"
      data-target="#navbarSupportedContent"
      aria-controls="navbarSupportedContent"
      aria-expanded="false"
      aria-label="Toggle navigation"
    >
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav mr-auto">
        <li class="nav-item active">
          <router-link class="nav-link" :to="{name: 'home'}" exact>
            Home
            <span class="sr-only">(current)</span>
          </router-link>
        </li>
        <template v-for="category in categories">
          <template v-if="category.children.length">
            <li class="nav-item dropdown" :key="category.slug">
              <router-link
                class="nav-link dropdown-toggle"
                :to="{name: 'categories-slug',params:{slug: category.slug}}"
                id="navbarDropdown"
                role="button"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
                exact
              >{{category.name}}</router-link>
              <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                <router-link
                  class="dropdown-item"
                  v-for="child in category.children"
                  :to="{name: 'categories-slug',params:{slug: child.slug}}"
                  :key="child.slug"
                  exact
                >{{ child.name }}</router-link>
              </div>
            </li>
          </template>
          <template v-else>
            <li class="nav-item" :key="category.slug">
              <router-link
                class="nav-link"
                :to="{name: 'categories-slug',params:{slug: category.slug}}"
                exact
              >{{ category.name }}</router-link>
            </li>
          </template>
        </template>
      </ul>
      <ul class="navbar-nav ml-auto">
        <li class="nav-item">
          <form class="form-inline ml-auto">
            <input
              class="form-control mr-sm-2"
              type="search"
              placeholder="Search"
              aria-label="Search"
            />
          </form>
        </li>
        <li class="nav-item">
          <router-link class="btn btn-light" :to="{name: 'login'}" exact>Login</router-link>
        </li>
      </ul>
    </div>
  </nav>
</template>
<script>
export default {
  name: "navbar",
  mounted() {
    this.$store.dispatch("fetchCategories");
  },
  computed: {
    categories() {
      return this.$store.getters.categories;
    }
  }
};
</script>
