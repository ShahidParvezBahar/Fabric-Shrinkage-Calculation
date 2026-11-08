export function apiUrl(path = '') {
  let base = window.AppConfig?.apiUrl;
  if (!base) {
    const pathSegments = window.location.pathname.split('/').filter(Boolean);
    if (pathSegments.length > 0 && pathSegments[pathSegments.length - 1] === 'public') {
      base = `${window.location.origin}/${pathSegments.join('/')}/api`;
    } else if (pathSegments.length > 0 && pathSegments[pathSegments.length - 1] === 'index.php') {
      pathSegments.pop();
      base = `${window.location.origin}/${pathSegments.join('/')}/api`;
    } else {
      base = `${window.location.origin}/api`;
    }
  }

  base = base.replace(/\/+$/, '');
  const cleanPath = path.replace(/^\/+/, '').replace(/^api\/+/, '');
  return cleanPath ? `${base}/${cleanPath}` : base;
}

