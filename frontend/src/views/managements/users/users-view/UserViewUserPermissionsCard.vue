<template>
  <b-card
    no-body
    class="border"
    style="width: 100%"
  >
    <b-card-header class="p-1">
      <b-card-title class="font-medium-2">
        <feather-icon
          icon="LockIcon"
          size="18"
        />
        <span class="align-middle ml-50">Права доступа</span>
      </b-card-title>
    </b-card-header>
    <b-card-body>
      <v-select
        v-model="permissions"
        multiple
        label="name"
        disabled
        :options="permissions"
      />
    </b-card-body>
  </b-card>
</template>

<script>
import {
  BCard, BCardHeader, BCardBody, BCardTitle,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import vSelect from 'vue-select'

export default {
  directives: {
    Ripple,
  },
  components: {
    BCard,
    BCardHeader,
    BCardTitle,
    BCardBody,
    vSelect,
  },
  props: {
    permissions: {
      type: Array,
      required: true,
    },
  },
}
</script>

<style lang="scss">
@import '~@core/scss/vue/libs/vue-select.scss';

.vs--disabled {
  cursor: pointer;
  .vs__dropdown-toggle {
    background-color: rgba(0, 0, 0, 0);
    cursor: pointer;
  }
  .vs__selected {
    background-color: #7367f0;
    cursor: pointer;
  }
  .vs__search {
    cursor: pointer;
    background-color: rgba(0, 0, 0, 0);
  }
}
</style>
