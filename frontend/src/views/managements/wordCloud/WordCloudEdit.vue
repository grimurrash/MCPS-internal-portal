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
          Редактирование облака слов
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
              label-for="eventData"
            >
              <b-form-input
                id="eventData"
                v-model="itemData.eventName"
                autofocus
                :state="getValidationState(validationContext)"
                trim
                placeholder="Наименование мероприятия"
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="Вопрос"
            rules="required"
          >
            <b-form-group
              label="Вопрос *"
              label-for="question"
            >
              <b-form-textarea
                id="question"
                v-model="itemData.question"
                autofocus
                :state="getValidationState(validationContext)"
                trim
                placeholder="Вопрос"
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <validation-provider
            #default="validationContext"
            name="Цветовое оформление текста *"
            rules="required"
          >
            <b-form-group
              label="Цветовое оформление текста"
              label-for="wordColor"
              :state="getValidationState(validationContext)"
            >
              <v-select
                v-model="itemData.wordColor"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="wordColorOptions"
                :reduce="val => val.value"
                :clearable="false"
                input-id="wordColor"
                placeholder="Цвет слов в облаке слов"
              />
              <b-form-invalid-feedback :state="getValidationState(validationContext)">
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <b-form-group
            label="Проверка на уникальность"
            label-for="isUniqueEdit"
          >
            <b-form-checkbox
              id="isUniqueEdit"
              v-model="itemData.isUnique"
              class="custom-control-primary"
              name="isUnique"
              switch
            >
              <span class="switch-icon-left">
                <feather-icon icon="UserCheckIcon" />
              </span>
              <span class="switch-icon-right">
                <feather-icon icon="GitlabIcon" />
              </span>
            </b-form-checkbox>
          </b-form-group>

          <b-form-group
            label="Заголовок страницы облака слова"
            label-for="pageTitle"
          >
            <b-form-input
              id="pageTitle"
              v-model="itemData.pageTitle"
              trim
              placeholder="Заголовок страницы"
            />
          </b-form-group>

          <validation-provider
            #default="validationContext"
            name="Фоновое изображение *"
            rules="required"
          >
            <b-form-group
              label="Фоновое изображение"
              label-for="backgroundImage"
            >
              <b-form-input
                id="backgroundImage"
                v-model="itemData.backgroundImage"
                :state="getValidationState(validationContext)"
                trim
                placeholder="Ссылка на фоновое изображение"
              />
              <b-form-invalid-feedback :state="getValidationState(validationContext)">
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <b-form-group
            label="Логотип"
            label-for="logo"
          >
            <b-form-input
              id="logo"
              v-model="itemData.logo"
              trim
              placeholder="Ссылка на логотип"
            />
          </b-form-group>

          <b-form-group
            label="Слова исключения"
            label-for="exceptionWords"
          >
            <b-form-textarea
              id="exceptionWords"
              v-model="itemData.exceptionWords"
              trim
              placeholder="Слова исключения, через запятую"
            />
          </b-form-group>

          <b-form-group
            label="Дополнительный CSS"
            label-for="additionalCss"
          >
            <b-form-textarea
              id="additionalCss"
              v-model="itemData.additionalCss"
              trim
              placeholder="Дополнительный CSS"
            />
          </b-form-group>

          <b-form-group
            label="Минимальный размер текста"
            label-for="fontSizeSmall"
          >
            <b-form-input
              id="fontSizeSmall"
              v-model="itemData.fontSizeSmall"
              trim
              placeholder="Минимальный размер текста"
            />
          </b-form-group>

          <b-form-group
            label="Максимальный размер текста"
            label-for="fontSizeLarge"
          >
            <b-form-input
              id="fontSizeLarge"
              v-model="itemData.fontSizeLarge"
              trim
              placeholder="Максимальный размер текста"
            />
          </b-form-group>

          <b-form-group
            label="Показывать кол-во ответов"
            label-for="showCountsEdit"
          >
            <b-form-checkbox
              id="showCountsEdit"
              v-model="itemData.showCounts"
              class="custom-control-primary"
              name="showCounts"
              switch
            >
              <span class="switch-icon-left">
                <feather-icon icon="UserCheckIcon" />
              </span>
              <span class="switch-icon-right">
                <feather-icon icon="GitlabIcon" />
              </span>
            </b-form-checkbox>
          </b-form-group>

          <b-form-group
            label="Размер кол-ва ответов"
            label-for="countSize"
          >
            <b-form-input
              id="countSize"
              v-model="itemData.countSize"
              trim
              placeholder="Размер кол-ва ответов"
            />
          </b-form-group>

          <b-form-group
            label="Угол поворота текста"
            label-for="angle"
          >
            <b-form-input
              id="angle"
              v-model="itemData.angle"
              trim
              placeholder="Угол поворота текста"
            />
          </b-form-group>

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
  BSidebar, BForm, BButton, BFormGroup, BFormInput, BFormInvalidFeedback, BFormTextarea, BFormCheckbox,
} from 'bootstrap-vue'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import Ripple from 'vue-ripple-directive'
import { ref } from '@vue/composition-api'
import vSelect from 'vue-select'
import axios from '@/libs/axios'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BSidebar,
    BForm,
    BButton,
    BFormGroup,
    BFormInput,
    BFormCheckbox,
    BFormInvalidFeedback,
    BFormTextarea,
    vSelect,
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
      if (newItem.id === 0) return
      this.itemData = {
        id: newItem.id,
        eventName: newItem.eventName,
        question: newItem.question,
        backgroundImage: newItem.backgroundImage,
        wordColor: newItem.wordColor,
        exceptionWords: newItem.exceptionWords,
        pageTitle: newItem.pageTitle,
        logo: newItem.logo,
        isUnique: newItem.isUnique,
        additionalCss: newItem.additionalCss,
        fontSizeSmall: newItem.fontSizeSmall,
        fontSizeLarge: newItem.fontSizeLarge,
        showCounts: newItem.showCounts,
        countSize: newItem.countSize,
        angle: newItem.angle,
      }
    },
  },
  methods: {
    onSubmit() {
      this.$refs.refFormObserver.validate().then(success => {
        if (!success) {
          return
        }
        axios.post(`/events/wordcloud/${this.itemData.id}`, {
          eventName: this.itemData.eventName,
          question: this.itemData.question,
          backgroundImage: this.itemData.backgroundImage,
          wordColor: this.itemData.wordColor,
          exceptionWords: this.itemData.exceptionWords,
          pageTitle: this.itemData.pageTitle,
          logo: this.itemData.logo,
          isUnique: this.itemData.isUnique,
          additionalCss: this.itemData.additionalCss,
          fontSizeSmall: this.itemData.fontSizeSmall,
          fontSizeLarge: this.itemData.fontSizeLarge,
          showCounts: this.itemData.showCounts,
          countSize: this.itemData.countSize,
          angle: this.itemData.angle,
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
      eventName: '',
      question: '',
      backgroundImage: 'https://portal.cpvs.moscow/public/images/backgroud.svg',
      wordColor: 'pastel1',
      exceptionWords: '',
      pageTitle: 'Облако слов',
      logoPanelImg: '',
      isUnique: false,
      additionalCss: '',
      fontSizeSmall: 30,
      fontSizeLarge: 120,
      showCounts: false,
      countSize: 20,
      angle: 0,
    }

    const wordColorOptions = [
      // Qualitative Color Ramp
      { value: 'accent', label: 'Diverging Color Ramp - accent' },
      { value: 'dark2', label: 'Diverging Color Ramp - dark2' },
      { value: 'paired', label: 'Diverging Color Ramp - paired' },
      { value: 'pastel1', label: 'Diverging Color Ramp - pastel1' },
      { value: 'pastel2', label: 'Diverging Color Ramp - pastel2' },
      { value: 'set1', label: 'Diverging Color Ramp - set1' },
      { value: 'set2', label: 'Diverging Color Ramp - set2' },
      { value: 'set3', label: 'Diverging Color Ramp - set3' },
      { value: 'buGn', label: 'Sequential Multi-hue Color Map - buGn' },
      { value: 'buPu', label: 'Sequential Multi-hue Color Map - buPu' },
      { value: 'gnBu', label: 'Sequential Multi-hue Color Map - gnBu' },
      { value: 'orRd', label: 'Sequential Multi-hue Color Map - orRd' },
      { value: 'puBu', label: 'Sequential Multi-hue Color Map - puBu' },
      { value: 'puBuGn', label: 'Sequential Multi-hue Color Map - puBuGn' },
      { value: 'puRd', label: 'Sequential Multi-hue Color Map - puRd' },
      { value: 'rdPu', label: 'Sequential Multi-hue Color Map - rdPu' },
      { value: 'ylGn', label: 'Sequential Multi-hue Color Map - ylGn' },
      { value: 'ylGnBu', label: 'Sequential Multi-hue Color Map - ylGnBu' },
      { value: 'ylOrBr', label: 'Sequential Multi-hue Color Map - ylOrBr' },
      { value: 'ylOrRd', label: 'Sequential Multi-hue Color Map - ylOrRd' },
      { value: 'blues', label: 'Sequential Single Color Map - blues' },
      { value: 'greens', label: 'Sequential Single Color Map - greens' },
      { value: 'greys', label: 'Sequential Single Color Map - greys' },
      { value: 'oranges', label: 'Sequential Single Color Map - oranges' },
      { value: 'purples', label: 'Sequential Single Color Map - purples' },
      { value: 'reds', label: 'Sequential Single Color Map - reds' },
      { value: 'brBr', label: 'Diverging Color Ramp - brBr' },
      { value: 'piYG', label: 'Diverging Color Ramp - piYG' },
      { value: 'pRGn', label: 'Diverging Color Ramp - pRGn' },
      { value: 'puOr', label: 'Diverging Color Ramp - puOr' },
      { value: 'rdBu', label: 'Diverging Color Ramp - rdBu' },
      { value: 'rdGy', label: 'Diverging Color Ramp - rdGy' },
      { value: 'rdYlBu', label: 'Diverging Color Ramp - rdYlBu' },
      { value: 'rdYlGn', label: 'Diverging Color Ramp - rdYlGn' },
      { value: 'spectral', label: 'Diverging Color Ramp - spectral' },
    ]

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
      wordColorOptions,
      refFormObserver,
      getValidationState,
      resetForm,
    }
  },
}
</script>

<style lang="scss">
@import '~@core/scss/vue/libs/vue-select.scss';
</style>
