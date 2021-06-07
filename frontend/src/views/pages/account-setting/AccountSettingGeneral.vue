<template>
  <b-card>

    <!-- media -->
    <b-media no-body>
      <b-avatar
        ref="previewEl"
        :src="optionsLocal.avatar"
        :text="avatarText(optionsLocal.fullName)"
        :variant="`light-primary`"
        primary
        size="80px"
        rounded
      />

      <b-media-body class="mt-75 ml-75">
        <!-- upload button -->
        <b-button
          v-ripple.400="'rgba(255, 255, 255, 0.15)'"
          variant="primary"
          size="sm"
          class="mb-75 mr-75"
          @click="$refs.refInputEl.click()"
        >
          Загрузить
        </b-button>
        <input
          ref="refInputEl"
          type="file"
          accept=".jpg, .png, .gif"
          class="d-none"
          @input="inputImageRenderer"
        >
        <!--/ upload button -->

        <!-- reset -->
        <b-button
          v-ripple.400="'rgba(186, 191, 199, 0.15)'"
          variant="outline-secondary"
          size="sm"
          class="mb-75 mr-75"
          @click="resetImage"
        >
          Очистить
        </b-button>
        <!--/ reset -->
        <b-card-text>Допускается JPG, GIF или PNG. Максимальный размер 1 Мб</b-card-text>
      </b-media-body>
    </b-media>
    <!--/ media -->

    <!-- form -->
    <b-form class="mt-2">
      <b-row>
        <b-col sm="6">
          <b-form-group
            label="ФИО"
            label-for="account-username"
          >
            <b-form-input
              v-model="optionsLocal.fullName"
              placeholder="ФИО"
              required
              name="fullName"
            />
          </b-form-group>
        </b-col>
        <b-col sm="6">
          <b-form-group
            label="E-mail"
            label-for="account-e-mail"
          >
            <b-form-input
              v-model="optionsLocal.email"
              name="email"
              required
              disabled
              placeholder="Email"
            />
          </b-form-group>
        </b-col>

        <b-col cols="12">
          <b-button
            v-ripple.400="'rgba(255, 255, 255, 0.15)'"
            variant="primary"
            class="mt-2 mr-1"
            @click="saveUserData"
          >
            Сохранить изменения
          </b-button>
          <b-button
            v-ripple.400="'rgba(186, 191, 199, 0.15)'"
            variant="outline-secondary"
            type="reset"
            class="mt-2"
            @click.prevent="resetForm"
          >
            Сброс
          </b-button>
        </b-col>
      </b-row>
    </b-form>
  </b-card>
</template>

<script>
import {
  BButton, BForm, BFormGroup, BFormInput, BRow, BCol, BCard, BCardText, BMedia, BMediaBody, BAvatar,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import { avatarText } from '@core/utils/filter'
import { ref } from '@vue/composition-api'
import { useInputImageRenderer } from '@core/comp-functions/forms/form-utils'
import useUsersList from '@/views/managements/users/users-list/useUsersList'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import axios from '@/libs/axios'

export default {
  components: {
    BButton,
    BForm,
    BFormGroup,
    BFormInput,
    BRow,
    BAvatar,
    BCol,
    BCard,
    BCardText,
    BMedia,
    BMediaBody,
  },
  directives: {
    Ripple,
  },
  props: {
    generalData: {
      type: Object,
      default: () => {},
    },
  },
  data() {
    return {
      profileFile: null,
    }
  },
  methods: {
    resetForm() {
      this.optionsLocal = JSON.parse(JSON.stringify(this.generalData))
    },
    resetImage() {
      this.isChangeAvatar = true
      this.optionsLocal.avatar = null
    },
    saveUserData() {
      const formData = new FormData()
      formData.append('fullName', this.optionsLocal.fullName)
      formData.append('isUploadAvatar', this.isChangeAvatar)
      if (this.isChangeAvatar) formData.append('avatar', this.refInputEl.files[0])
      axios.post('account-setting/general/save-changes', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
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
              title: error.response.data.message,
              variant: 'danger',
              icon: 'AlertCircleIcon',
            },
          })
        })
    },
  },
  setup(props) {
    const { resolveUserRoleVariant } = useUsersList()

    const refInputEl = ref(null)
    const previewEl = ref(null)
    const optionsLocal = ref(JSON.parse(JSON.stringify(props.generalData)))
    const isChangeAvatar = ref(false)

    const { inputImageRenderer } = useInputImageRenderer(refInputEl, base64 => {
      isChangeAvatar.value = true
      // eslint-disable-next-line no-param-reassign
      optionsLocal.value.avatar = base64
    })

    return {
      resolveUserRoleVariant,
      optionsLocal,
      avatarText,
      refInputEl,
      previewEl,
      isChangeAvatar,
      inputImageRenderer,
    }
  },
}
</script>
