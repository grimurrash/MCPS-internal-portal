import useJwt from '@core/auth/jwt/useJwt'
import axios from '@axios'

const jwtConfig = {
  loginEndpoint: '/auth/login',
  registerEndpoint: '/auth/register',
  refreshEndpoint: '/auth/refresh-token',
  logoutEndpoint: '/auth/logout',

  // This will be prefixed in authorization header with token
  // e.g. Authorization: Bearer <token>
  tokenType: 'Bearer',

  // Value of this property will be used as key to store JWT token in storage
  storageTokenKeyName: 'MPSCAccessToken',
  storageExpiresAt: 'MPSCExpiresAt',
}

const { jwt } = useJwt(axios, jwtConfig)
export default jwt
