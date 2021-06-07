import axios from '@axios'

export default {
  namespaced: true,
  state: {},
  getters: {},
  mutations: {},
  actions: {
    fetchOptions() {
      return new Promise((resolve, reject) => {
        axios
          .get('/management/rolesAndPermissions')
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchUsers(ctx, queryParams) {
      return new Promise((resolve, reject) => {
        axios
          .get('/management/users', { params: queryParams })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchUser(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .get(`/management/users/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchEditUser(ctx, { formData, id }) {
      return new Promise((resolve, reject) => {
        axios
          .post(`/management/users/${id}`, formData, {
            headers: {
              'Content-Type': 'multipart/form-data',
            },
          })
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    fetchDeleteUser(ctx, { id }) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`/management/users/${id}`)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
    addUser(ctx, userData) {
      return new Promise((resolve, reject) => {
        axios
          .post('/management/users', userData)
          .then(response => resolve(response))
          .catch(error => reject(error))
      })
    },
  },
}
