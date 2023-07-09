<script>

import Ripple from 'vue-ripple-directive'
import {
  BButton, BFormGroup, BFormInput, BFormTextarea, BSidebar,
} from 'bootstrap-vue'
import { required } from '@core/utils/validations/validations'

export default {
  name: 'ViewProject',
  components: {
    BFormTextarea,
    BSidebar,
    BFormGroup,
    BFormInput,
    BButton,
  },
  directives: {
    Ripple,
  },
  model: {
    prop: 'isViewProject',
    event: 'update:is-view-project',
  },
  props: {
    isViewProject: {
      type: Boolean,
      required: true,
    },
    selectProject: {
      type: Object,
      required: true,
    },
    curatorOptions: {
      type: Array,
      required: true,
    },
    employeeOptions: {
      type: Array,
      required: true,
    },
    statusOptions: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      required,
    }
  },
}
</script>

<template>
  <b-sidebar
    id="add-new-user-sidebar"
    :visible="isViewProject"
    bg-variant="white"
    sidebar-class="sidebar-lg"
    shadow
    backdrop
    no-header
    right
    @change="(val) => $emit('update:is-view-project', val)"
  >
    <template #default="{ hide }">

      <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
        <h5 class="mb-0">
          Проект
        </h5>

        <feather-icon
          class="ml-1 cursor-pointer"
          icon="XIcon"
          size="16"
          @click="hide"
        />

      </div>

      <div class="p-2">

        <b-form-group
          label="Наименование"
          label-for="name"
        >
          <b-form-input
            id="name"
            v-model="selectProject.name"
            readonly
          />
        </b-form-group>
        <b-form-group
          label="№ согласно календарному плану на учебный год "
          label-for="number"
        >
          <b-form-input
            id="number"
            v-model="selectProject.number"
            readonly
          />
        </b-form-group>
        <b-form-group
          label="сроки реализации, дата начало"
          label-for="start_date"
        >
          <b-form-input
            id="organizer_name"
            v-model="selectProject.start_date"
            readonly
          />
        </b-form-group>
        <b-form-group
          label="сроки реализации, дата окончание"
          label-for="end_date"
        >
          <b-form-input
            id="organizer_name"
            v-model="selectProject.end_date"
            readonly
          />
        </b-form-group>

        <b-form-group
          label="Организатор"
          label-for="organizer_name"
        >
          <b-form-input
            id="organizer_name"
            v-model="selectProject.organizer_name"
            readonly
          />
        </b-form-group>

        <b-form-group
          label="Ответственный за проект"
          label-for="responsible_employee_id"
        >
          <b-form-input
            id="responsible_employee_id"
            v-model="selectProject.responsible_employee_name"
            readonly
          />
        </b-form-group>

        <b-form-group
          label="Краткое описание проект"
          label-for="description"
        >
          <b-form-textarea
            id="description"
            v-model="selectProject.description"
            readonly
          />
        </b-form-group>
        <b-form-group
          label="Ключевые показатели"
          label-for="metrics"
        >
          <b-form-textarea
            id="metrics"
            v-model="selectProject.metrics"
            readonly
          />
        </b-form-group>

        <b-form-group
          label="Планируемый охват участников"
          label-for="planned_coverage"
        >
          <b-form-input
            id="planned_coverage"
            v-model="selectProject.planned_coverage"
            type="number"
            readonly
          />
        </b-form-group>

        <b-form-group
          label="Фактический охват участников"
          label-for="actual_coverage"
        >
          <b-form-input
            id="actual_coverage"
            v-model="selectProject.actual_coverage"
            type="number"
            readonly
          />
        </b-form-group>

        <b-form-group
          label="Курирующий заместитель директора"
          label-for="curator_id"
        >
          <b-form-input
            id="actual_coverage"
            v-model="selectProject.curator_name"
            readonly
          />

        </b-form-group>
        <b-form-group
          label="Статус проекта"
          label-for="status"
        >
          <b-form-input
            id="actual_coverage"
            v-model="statusOptions.find((item) => item.value === selectProject.status).label"
            readonly
          />
        </b-form-group>

        <b-button
          v-ripple.400="'rgba(186, 191, 199, 0.15)'"
          type="button"
          variant="outline-secondary"
          @click="hide"
        >
          Закрыть
        </b-button>
      </div>
    </template>
  </b-sidebar>
</template>

<style lang="scss">
#add-new-user-sidebar {
  .vs__dropdown-menu {
    max-height: 400px !important;
  }
}
</style>
