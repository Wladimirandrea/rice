import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

// Habilitar logs de Pusher en desarrollo para debug
if (import.meta.env.DEV) {
    Pusher.logToConsole = true
}

const echo = new Echo({
    broadcaster:        'reverb',
    key:                import.meta.env.VITE_REVERB_APP_KEY,
    wsHost:             import.meta.env.VITE_REVERB_HOST,
    wsPort:             import.meta.env.VITE_REVERB_PORT       ?? 8080,
    wssPort:            import.meta.env.VITE_REVERB_PORT       ?? 8080,
    forceTLS:           (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports:  ['ws', 'wss'],
    disableStats:       true,

    // ── Reconexión automática ──────────────────────────────
    activityTimeout:    30000,   // si no hay ping en 30s → desconectado
    pongTimeout:        10000,   // espera 10s el pong antes de reconectar
    unavailableTimeout: 10000,   // intenta reconectar tras 10s sin conexión
})

// ── Listeners de estado de conexión ───────────────────────
echo.connector.pusher.connection.bind('connected', () => {
    console.info('[Reverb] ✅ Conectado')
})

echo.connector.pusher.connection.bind('disconnected', () => {
    console.warn('[Reverb] ❌ Desconectado — intentando reconectar...')
})

echo.connector.pusher.connection.bind('unavailable', () => {
    console.warn('[Reverb] ⚠️ Servidor no disponible — reintentando...')
})

echo.connector.pusher.connection.bind('failed', () => {
    console.error('[Reverb] 💥 Conexión fallida — sin soporte WebSocket')
})

echo.connector.pusher.connection.bind('error', (err) => {
    console.error('[Reverb] Error:', err)
})

export default echo