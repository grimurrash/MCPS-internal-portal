<template>
  <div>

    <!-- Media -->
    <b-media class="mb-2">
      <template #aside>
        <b-avatar
          ref="previewEl"
          :src="userData.avatar"
          :text="avatarText(userData.fullName)"
          :variant="`light-${resolveUserRoleVariant(userData.role)}`"
          size="90px"
          rounded
        />
      </template>
      <h4 class="mb-1">
        {{ userData.fullName }}
      </h4>
      <div class="d-flex flex-wrap">
        <b-button
          variant="primary"
          @click="$refs.refInputEl.click()"
        >
          <input
            ref="refInputEl"
            type="file"
            class="d-none"
            @input="inputImageRenderer"
          >
          <span class="d-none d-sm-inline">Загрузить</span>
          <feather-icon
            icon="EditIcon"
            class="d-inline d-sm-none"
          />
        </b-button>
        <b-button
          variant="outline-secondary"
          class="ml-1"
          @click="userData.avatar = ''; isChangeAvatar = true"
        >
          <span class="d-none d-sm-inline">Очистить</span>
          <feather-icon
            icon="TrashIcon"
            class="d-inline d-sm-none"
          />
        </b-button>
      </div>
    </b-media>

    <!-- User Info: Input Fields -->
    <validation-observer ref="editForm">
      <b-form
        class="p-2"
        @submit.prevent
      >
        <b-row>

          <!-- Field: Full Name -->
          <b-col
            cols="12"
            md="4"
          >
            <validation-provider
              #default="{ errors }"
              name="ФИО"
              rules="required"
            >
              <b-form-group
                label="ФИО"
                label-for="full-name"
              >
                <b-form-input
                  id="full-name"
                  v-model="userData.fullName"
                  :state="errors.length > 0 ? false:null"
                />
              </b-form-group>
              <small class="text-danger">{{ errors[0] }}</small>
            </validation-provider>
          </b-col>

          <!-- Field: Email -->
          <b-col
            cols="12"
            md="4"
          >
            <validation-provider
              #default="{ errors }"
              name="Email"
              rules="required|email"
            >
              <b-form-group
                label="Email"
                label-for="email"
              >
                <b-form-input
                  id="email"
                  v-model="userData.email"
                  type="email"
                  :state="errors.length > 0 ? false:null"
                />
              </b-form-group>
              <small class="text-danger">{{ errors[0] }}</small>
            </validation-provider>
          </b-col>

          <!-- Field: Role -->
          <b-col
            cols="12"
            md="4"
          >
            <validation-provider
              #default="{ errors }"
              name="Роль"
              rules="required"
            >
              <b-form-group
                label="Роль"
                label-for="user-role"
              >
                <v-select
                  v-model="userData.role_id"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  :options="roleOptions"
                  :reduce="val => val.value"
                  :clearable="false"
                  :state="errors.length > 0 ? false:null"
                  input-id="user-role"
                />
              </b-form-group>
              <small class="text-danger">{{ errors[0] }}</small>
            </validation-provider>
          </b-col>

        </b-row>
      </b-form>
    </validation-observer>

    <!-- PERMISSION TABLE -->
    <b-card
      no-body
      class="border mt-1"
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
          v-model="selectPermission"
          multiple
          label="name"
          value="id"
          :options="permissionsOptions"
        />
      </b-card-body>
    </b-card>

    <!-- Action Buttons -->
    <b-button
      variant="primary"
      class="mb-1 mb-sm-0 mr-0 mr-sm-1"
      :block="$store.getters['app/currentBreakPoint'] === 'xs'"
      @click.prevent="saveUserData"
    >
      Сохранить изменения
    </b-button>
    <b-button
      variant="outline-secondary"
      type="reset"
      :block="$store.getters['app/currentBreakPoint'] === 'xs'"
      @click="hasHistory()
        ? $router.go(-1)
        : $router.push('/')"
    >
      Назад
    </b-button>
  </div>
</template>

<script>
import {
  BButton, BMedia, BAvatar, BRow, BCol, BFormGroup, BFormInput, BForm, BCard, BCardHeader, BCardTitle, BCardBody,
} from 'bootstrap-vue'
import { avatarText } from '@core/utils/filter'
import vSelect from 'vue-select'
import { useInputImageRenderer } from '@core/comp-functions/forms/form-utils'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required, email } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import { ref } from '@vue/composition-api'
import store from '@/store'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import useUsersList from '../users-list/useUsersList'

export default {
  components: {
    BButton,
    BMedia,
    BAvatar,
    BRow,
    BCol,
    BFormGroup,
    BFormInput,
    BForm,
    BCard,
    BCardHeader,
    BCardTitle,
    BCardBody,
    vSelect,

    // Form Validation
    ValidationProvider,
    ValidationObserver,
  },
  props: {
    userData: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      required,
      email,
      avatarFile: null,
    }
  },
  created() {
    console.log(this.userData.ability)
    this.selectPermission = this.userData.ability.map(item => ({
      id: item.id,
      name: item.name,
    }))
  },
  methods: {
    hasHistory() {
      return window.history.length > 2
    },
    saveUserData() {
      this.$refs.editForm.validate().then(success => {
        if (success) {
          const formData = new FormData()
          formData.append('avatar', this.refInputEl.files[0])
          formData.append('fullName', this.userData.fullName)
          formData.append('email', this.userData.email)
          formData.append('role_id', this.userData.role_id)
          formData.append('permissions', JSON.stringify(this.selectPermission))
          formData.append('isUploadAvatar', this.isChangeAvatar)
          store.dispatch('app-user/fetchEditUser', {
            id: this.userData.id,
            formData,
          })
            .then(response => {
              this.$toast({
                component: ToastificationContent,
                position: 'top-right',
                props: {
                  title: response.data.message,
                  variant: 'success',
                  icon: 'CheckIcon',
                },
              })
            })
            .catch(error => {
              this.$toast({
                component: ToastificationContent,
                position: 'top-right',
                props: {
                  title: error.data.message,
                  variant: 'danger',
                  icon: 'AlertCircleIcon',
                },
              })
            })
        }
      })
    },
  },
  setup(props) {
    const { resolveUserRoleVariant } = useUsersList()

    const roleOptions = ref([])
    const permissionsOptions = ref([])
    const selectPermission = ref([])

    store.dispatch('app-user/fetchOptions')
      .then(response => {
        const { roles, permissions } = response.data
        roleOptions.value = roles.map(item => ({
          value: item.id,
          label: item.name,
        }))
        permissionsOptions.value = permissions.map(item => ({
          id: item.id,
          name: item.name,
        }))
      })

    // ? Demo Purpose => Update image on click of update
    const refInputEl = ref(null)
    const previewEl = ref(null)
    const isChangeAvatar = ref(false)
    const { inputImageRenderer } = useInputImageRenderer(refInputEl, base64 => {
      isChangeAvatar.value = true
      // eslint-disable-next-line no-param-reassign
      props.userData.avatar = base64
    })

    const {
      refFormObserver,
      getValidationState,
      resetForm,
    } = formValidation()

    return {
      resolveUserRoleVariant,
      avatarText,
      roleOptions,
      permissionsOptions,
      selectPermission,
      isChangeAvatar,

      refFormObserver,
      getValidationState,
      resetForm,

      //  ? Demo - Update Image on click of update button
      refInputEl,
      previewEl,
      inputImageRenderer,
    }
  },
}
</script>

<style lang="scss">
@import '~@core/scss/vue/libs/vue-select.scss';
</style>
