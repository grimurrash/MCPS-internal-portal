<template>
  <b-sidebar
    id="add-sidebar"
    :visible="isEditSidebarActive"
    bg-variant="white"
    sidebar-class="sidebar-lg"
    shadow
    backdrop
    no-header
    right
    @hidden="resetForm"
    @change="(val) => $emit('update:is-edit-sidebar-active', val)"
  >
    <template #default="{ hide }">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
        <h5 class="mb-0">
          Редактирование таблицы органайзера
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
              label-for="organizerItemEditName"
            >
              <b-form-input
                id="organizerItemEditName"
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

          <validation-provider
            #default="validationContext"
            name="Ссылка на таблицу/страницу"
            rules="required"
          >
            <b-form-group
              label="Ссылка на таблицу/страницу *"
              label-for="organizerItemEditLink"
            >
              <b-form-input
                id="organizerItemEditLink"
                v-model="itemData.link"
                autofocus
                :state="getValidationState(validationContext)"
                trim
                placeholder="Ссылка на таблицу/страницу"
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
              Сохранить
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
    prop: 'isEditSidebarActive',
    event: 'update:is-edit-sidebar-active',
  },
  props: {
    isEditSidebarActive: {
      type: Boolean,
      required: true,
    },
    editItem: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      required,
    }
  },
  watch: {
    editItem(newItem) {
      console.log(newItem)
      if (newItem.id === 0) return
      this.itemData = {
        id: newItem.id,
        name: newItem.name,
        menu_id: newItem.menu_id,
        link: newItem.link,
      }
    },
  },
  methods: {
    onSubmit() {
      this.$refs.refFormObserver.validate().then(success => {
        if (!success) {
          return
        }
        axios.post(`/organizer/item/${this.itemData.id}`, {
          name: this.itemData.name,
          menu_id: this.itemData.menu_id,
          link: this.itemData.link,
        }).then(() => {
          this.$emit('refetch-data')
          this.$toast({
            component: ToastificationContent,
            props: {
              title: 'Изменения сохранены!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        }).catch(() => {
          this.$toast({
            component: ToastificationContent,
            props: {
              title: 'Ошибка при редактировании облака слов',
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
      id: 0,
      name: '',
      menu_id: 0,
      link: '',
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
