export default function registerHelpers(app) {
  const modules = import.meta.glob('./*.js', { eager: true })

  for (const path in modules) {
    const helperModule = modules[path]

    for (const [name, fn] of Object.entries(helperModule)) {
      if (typeof fn === 'function') {
        app.config.globalProperties[`$${name}`] = fn  // Daftarkan ke global Vue
      }
    }
  }
}
