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
            placeholder="Выбрать отдел"
            class="w-100"
            :reduce="val => val.value"
            @input="(val) => $emit('update:department-filter', val)"
          />
        </b-col>

        <!-- Сотрудники -->
        <b-col
          cols="12"
          md="3"
          class="mb-md-0 mb-2"
        >
          <label>Сотрудник</label>
          <v-select
            :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
            :value="employeeFilter"
            :options="employeeOptions"
            placeholder="Выбрать сотрудника"
            class="w-100"
            :reduce="val => val.value"
            @input="(val) => $emit('update:employee-filter', val)"
          />
        </b-col>

        <!-- Дата -->
        <b-col
          cols="12"
          md="2"
          class="mb-md-0 mb-2"
        >
          <label>Дата</label>
          <flat-pickr
            :value="selectData"
            class="form-control"
            placeholder="Дата"
            :config="{ mode: 'range'}"
            @input="(val) => $emit('update:select-data', val)"
          />
        </b-col>

        <!-- Опоздангие -->
        <b-col
          v-if="isLateArrival !== null"
          cols="12"
          md="2"
          class="mb-md-0 mb-2"
        >
          <label>Отсутствие на рабочем месте</label>
          <b-form-checkbox
            v-model="isLateArrivalModel"
            class="custom-control-warning"
            name="check-button"
            switch
          >
            <span class="switch-icon-left">
              <feather-icon icon="ShieldIcon" />
            </span>
            <span class="switch-icon-right">
              <feather-icon icon="ShieldOffIcon" />
            </span>
          </b-form-checkbox>
        </b-col>
      </b-row>
    </b-card-body>
  </b-card>
</template>

<script>
import {
  BCard, BCardHeader, BCardBody, BRow, BCol, BFormCheckbox,
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
    BFormCheckbox,
    flatPickr,
    vSelect,
  },
  props: {
    departmentFilter: {
      type: [String, null],
      default: null,
    },
    employeeFilter: {
      type: [Number, null],
      default: null,
    },
    selectData: {
      type: [String, null],
      default: null,
    },
    isLateArrival: {
      type: [Boolean, null],
      default: false,
    },
    departmentOptions: {
      type: Array,
      required: true,
    },
    employeeOptions: {
      type: Array,
      required: true,
    },
  },
  data: () => ({
    isLateArrivalModel: false,
  }),
  watch: {
    isLateArrivalModel() {
      this.$emit('update:isLateArrival', this.isLateArrivalModel)
    },
  },
}
</script>

<style lang="scss">
@import '~@core/scss/vue/libs/vue-select.scss';
@import '~@core/scss/vue/libs/vue-flatpicker.scss';
</style>
