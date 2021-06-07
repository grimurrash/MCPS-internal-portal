<template>
  <b-card no-body>
    <b-card-header class="pb-50">
      <h5>
        Фильтры
      </h5>
    </b-card-header>
    <b-card-body>
      <b-row>

        <!-- Отделы -->
        <b-col
          cols="12"
          md="8"
          class="mb-md-0 mb-2"
        >
          <label>Отдел</label>
          <v-select
            :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
            :value="departmentFilter"
            :options="departmentOptions"
            class="w-100"
            :reduce="val => val.value"
            @input="(val) => $emit('update:departmentFilter', val)"
          />
        </b-col>

        <!-- Наличие внутреннего номера -->
        <b-col
          cols="12"
          md="4"
          class="mb-md-0 mb-2"
        >
          <label>Наличие внутреннего номера</label>
          <v-select
            :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
            :value="isHaveInternalCodeFilter"
            :options="isHaveInternalCodeOptions"
            class="w-100"
            :reduce="val => val.value"
            @input="(val) => $emit('update:isHaveInternalCodeFilter', val)"
          />
        </b-col>
      </b-row>
    </b-card-body>
  </b-card>
</template>

<script>
import {
  BCard, BCardHeader, BCardBody, BRow, BCol,
} from 'bootstrap-vue'
import vSelect from 'vue-select'

export default {
  components: {
    BRow,
    BCol,
    BCard,
    BCardHeader,
    BCardBody,
    vSelect,
  },
  props: {
    departmentFilter: {
      type: [String, null],
      default: null,
    },
    isHaveInternalCodeFilter: {
      type: [Number, null],
      default: null,
    },
    departmentOptions: {
      type: Array,
      required: true,
    },
    isHaveInternalCodeOptions: {
      type: Array,
      required: true,
    },
  },
}
</script>

<style lang="scss">
@import '~@core/scss/vue/libs/vue-select.scss';
</style>
