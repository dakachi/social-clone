import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
window.Pusher = Pusher

async function initEcho() {
  const resp = await fetch('/pusher/config', { credentials: 'same-origin' })
  if (!resp.ok) throw new Error('Cannot load Pusher config')
  const cfg = await resp.json()

  window.Echo = new Echo({
    broadcaster: 'pusher',
    key: cfg.key,
    cluster: cfg.cluster ?? 'mt1',
    wsHost: cfg.host ?? `ws-${cfg.cluster}.pusher.com`,
    wsPort: cfg.port ?? 80,
    wssPort: cfg.port ?? 443,
    forceTLS: (cfg.scheme ?? 'https') === 'https',
    enabledTransports: ['ws','wss'],
  })

  const teamId = document.querySelector('meta[name="team-id"]')?.content
  if (teamId) {
    window.Echo.private(`team.${teamId}.inbox`)
      .listen('.message.received', e => console.log('📥', e))
      .listen('.conversation.updated', e => console.log('♻️', e))
  }
}
initEcho().catch(console.error)