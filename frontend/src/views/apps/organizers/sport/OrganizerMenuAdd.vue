<template>
  <b-sidebar
    id="add-sidebar"
    :visible="isAddSidebarActive"
    bg-variant="white"
    sidebar-class="sidebar-lg"
    shadow
    backdrop
    no-header
    right
    @hidden="resetForm"
    @change="(val) => $emit('update:is-add-sidebar-active', val)"
  >
    <template #default="{ hide }">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
        <h5 class="mb-0">
          Добавление пункта меню органайзера
        </h5>

        <feather-icon
          class="ml-1 cursor-pointer"
          icon="XIcon"
          size="16"
          @click="hide"
        />
      </div>

      <!-- BODY -->
      <validation-observer
        #default="{ handleSubmit }"
        ref="refFormObserver"
      >
        <!-- Form -->
        <b-form
          class="p-2"
          @submit.prevent="handleSubmit(onSubmit)"
          @reset.prevent="resetForm"
        >

          <validation-provider
            #default="validationContext"
            name="Наименование"
            rules="required"
          >
            <b-form-group
              label="Наименование *"
              label-for="organizerMenuName"
            >
              <b-form-input
                id="organizerMenuName"
                v-model="itemData.name"
                autofocus
                :state="getValidationState(validationContext)"
                trim
                placeholder="Наименование"
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!-- Form Actions -->
          <div class="d-flex mt-2">
            <b-button
              v-ripple.400="'rgba(255, 255, 255, 0.15)'"
              variant="primary"
              class="mr-2"
              type="submit"
            >
              Добавить
            </b-button>
            <b-button
              v-ripple.400="'rgba(186, 191, 199, 0.15)'"
              type="button"
              variant="outline-secondary"
              @click="hide"
            >
              Отмена
            </b-button>
          </div>

        </b-form>
      </validation-observer>
    </template>
  </b-sidebar>
</template>

<script>
import {
  BSidebar, BForm, BButton, BFormGroup, BFormInput, BFormInvalidFeedback,
} from 'bootstrap-vue'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import Ripple from 'vue-ripple-directive'
import { ref } from '@vue/composition-api'
import axios from '@/libs/axios'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BSidebar,
    BForm,
    BButton,
    BFormGroup,
    BFormInput,
    BFormInvalidFeedback,
    // Form Validation
    ValidationProvider,
    ValidationObserver,
  },
  directives: {
    Ripple,
  },
  model: {
    prop: 'isAddSidebarActive',
    event: 'update:is-add-sidebar-active',
  },
  props: {
    isAddSidebarActive: {
      type: Boolean,
      required: true,
    },
    organizerId: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {
      required,
    }
  },
  methods: {
    onSubmit() {
      this.$refs.refFormObserver.validate().then(success => {
        if (!success) {
          return
        }
        axios.post('/organizer/menu', {
          name: this.itemData.name,
          organizer_id: this.organizerId,
        }).then(() => {
          this.$emit('refetch-data')
          this.$toast({
            component: ToastificationContent,
            props: {
              title: 'Органайзер обновлен',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        }).catch(() => {
          this.$toast({
            component: ToastificationContent,
            props: {
              title: 'Ошибка при добавлении пункта меню в органайзер',
              icon: 'AlertTriangleIcon',
              variant: 'danger',
            },
          })
        })
      })
    },
  },
  setup() {
    const blankItemData = {
      name: '',
    }

    const itemData = ref(JSON.parse(JSON.stringify(blankItemData)))
    const resetData = () => {
      itemData.value = JSON.parse(JSON.stringify(blankItemData))
    }

    const {
      refFormObserver,
      getValidationState,
      resetForm,
    } = formValidation(resetData)

    return {
      itemData,
      refFormObserver,
      getValidationState,
      resetForm,
    }
  },
}
</script>
