<template>
  <b-card no-body>
    <b-card-header class="pb-50">
      <h5>
        Фильтры
      </h5>
    </b-card-header>
    <b-card-body>
      <b-row>
        <b-col
          cols="12"
          md="3"
          class="mb-md-0 mb-2"
        >
          <label>Адрес</label>
          <v-select
            :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
            :value="executionAddressFilter"
            :options="executionAddressesOptions"
            class="w-100"
            :reduce="val => val.value"
            @input="(val) => $emit('update:executionAddressFilter', val)"
          />
        </b-col>

        <b-col
          cols="12"
          md="2"
          class="mb-md-0 mb-2"
        >
          <label>Категория</label>
          <v-select
            :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
            :value="categoryFilter"
            :options="categoriesOptions"
            class="w-100"
            :reduce="val => val.value"
            @input="(val) => $emit('update:categoryFilter', val)"
          />
        </b-col>

        <b-col
          cols="12"
          md="2"
          class="mb-md-0 mb-2"
        >
          <label>Статус</label>
          <v-select
            :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
            :value="statusFilter"
            :options="statusesOptions"
            class="w-100"
            :reduce="val => val.value"
            @input="(val) => $emit('update:statusFilter', val)"
          />
        </b-col>
        <b-col
          cols="12"
          md="3"
          class="mb-md-0 mb-2"
        >
          <label>Исполнитель</label>
          <v-select
            :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
            :value="executorFilter"
            :options="executorsOptions"
            class="w-100"
            :reduce="val => val.value"
            @input="(val) => $emit('update:executorFilter', val)"
          />
        </b-col>

        <b-col
          cols="12"
          md="2"
          class="mb-md-0 mb-2"
        >
          <label>Дата</label>
          <flat-pickr
            :value="selectedDate"
            class="form-control"
            placeholder="Дата"
            :config="{ mode: 'range'}"
            @input="(val) => $emit('update:selected-date', val)"
          />
        </b-col>
      </b-row>
    </b-card-body>
  </b-card>
</template>

<script>
import {
  BCard, BCardBody, BCardHeader, BCol, BRow,
} from 'bootstrap-vue'
import flatPickr from 'vue-flatpickr-component'
import vSelect from 'vue-select'

export default {
  components: {
    BRow,
    BCol,
    BCard,
    BCardHeader,
    BCardBody,
    vSelect,
    flatPickr,
  },
  props: {
    executionAddressFilter: {
      type: [Number, null],
      default: null,
    },
    categoryFilter: {
      type: [Number, null],
      default: null,
    },
    statusFilter: {
      type: [Number, null],
      default: null,
    },
    executorFilter: {
      type: [Number, null],
      default: null,
    },
    selectedDate: {
      type: [String, null],
      default: null,
    },
    executionAddressesOptions: {
      type: Array,
      required: true,
    },
    categoriesOptions: {
      type: Array,
      required: true,
    },
    statusesOptions: {
      type: Array,
      required: true,
    },
    executorsOptions: {
      type: Array,
      required: true,
    },
  },
}
</script>

<style lang="scss">
@import '~@core/scss/vue/libs/vue-select.scss';
@import '~@core/scss/vue/libs/vue-flatpicker.scss';
</style>
