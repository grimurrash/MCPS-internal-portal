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
          md="4"
          class="mb-md-0 mb-2"
        >
          <label>Тип документа</label>
          <v-select
            :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
            :value="documentTypeFilter"
            :options="documentTypeOptions"
            class="w-100"
            :reduce="val => val.value"
            @input="(val) => $emit('update:documentTypeFilter', val)"
          />
        </b-col>
        <b-col
          cols="12"
          md="4"
          class="mb-md-0 mb-2"
        >
          <label>Кол-во копий в одном документе</label>
          <v-select
            :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
            :value="isDoubleFilter"
            :options="isDoubleOptions"
            class="w-100"
            :reduce="val => val.value"
            @input="(val) => $emit('update:isDoubleFilter', val)"
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
    documentTypeFilter: {
      type: [Number, null],
      default: null,
    },
    isDoubleFilter: {
      type: [Number, null],
      default: null,
    },
    documentTypeOptions: {
      type: Array,
      required: true,
    },
    isDoubleOptions: {
      type: Array,
      required: true,
    },
  },
}
</script>

<style lang="scss">
@import '~@core/scss/vue/libs/vue-select.scss';
</style>
