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
          md="5"
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
          md="2"
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

        <b-col
          cols="12"
          md="2"
          class="mb-md-0 mb-2"
        >
          <label>Образование</label>
          <v-select
            :value="educationFilter"
            :options="educationOptions"
            class="w-100"
            :reduce="val => val.value"
            @input="(val) => $emit('update:educationFilter', val)"
          />
        </b-col>

        <b-col
          cols="12"
          md="1"
          class="mb-md-0 mb-2"
        >
          <label>Пол</label>
          <v-select
            :value="genderFilter"
            :options="genderOptions"
            class="w-100"
            :reduce="val => val.value"
            @input="(val) => $emit('update:genderFilter', val)"
          />
        </b-col>

        <b-col
          cols="12"
          md="2"
          class="mb-md-0 mb-2"
        >
          <label>Представитель учредителя</label>
          <v-select
            :value="isFoundersRepresentativeFilter"
            :options="isFoundersRepresentativeOptions"
            class="w-100"
            :reduce="val => val.value"
            @input="(val) => $emit('update:isFoundersRepresentativeFilter', val)"
          />
        </b-col>

        <b-col
          cols="12"
          md="2"
          class="mb-md-0 mb-2"
        >
          <label>Возраст от</label>
          <b-form-input
            v-model="ageFrom"
            class="w-100"
            type="number"
            placeholder="Возраст от"
            :reduce="val => val.value"
            @input="(val) => $emit('update:ageFromFilter', val)"
          />
        </b-col>

        <b-col
          cols="12"
          md="2"
          class="mb-md-0 mb-2"
        >
          <label>Возраст до</label>
          <b-form-input
            v-model="ageTo"
            class="w-100"
            type="number"
            placeholder="Возраст до"
            :reduce="val => val.value"
            @input="(val) => $emit('update:ageToFilter', val)"
          />
        </b-col>
      </b-row>
    </b-card-body>
  </b-card>
</template>

<script>
import {
  BCard, BCardHeader, BCardBody, BRow, BCol, BFormInput,
} from 'bootstrap-vue'
import vSelect from 'vue-select'

export default {
  components: {
    BFormInput,
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
    educationFilter: {
      type: [String, null],
      default: null,
    },
    educationOptions: {
      type: Array,
      required: true,
    },
    genderFilter: {
      type: [String, null],
      default: null,
    },
    genderOptions: {
      type: Array,
      required: true,
    },
    isFoundersRepresentativeFilter: {
      type: [Boolean, null],
      default: null,
    },
    isFoundersRepresentativeOptions: {
      type: Array,
      required: true,
    },
    ageFromFilter: {
      type: [String, null],
      default: null,
    },
    ageToFilter: {
      type: [String, null],
      default: null,
    },
  },
  data() {
    return {
      ageTo: '',
      ageFrom: '',
    }
  },
}
</script>

<style lang="scss">
@import '~@core/scss/vue/libs/vue-select.scss';
</style>
