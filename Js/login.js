/* Adaptado de login-page-10 de Colorlib. */
(() => {
  'use strict';
  const root = document.querySelector('.cl-login10');
  const tabs = [...root.querySelectorAll('[role="tab"]')];
  const panels = tabs.map(t => document.getElementById(t.getAttribute('aria-controls')));
  const live = root.querySelector('[data-live]');
  let busy = false;
  function select(i, focus = false) {
    if (busy) return;
    const from = tabs.findIndex(t => t.getAttribute('aria-selected') === 'true');
    tabs.forEach((t,k) => {
      t.setAttribute('aria-selected', String(k === i)); t.tabIndex = k === i ? 0 : -1;
      panels[k].hidden = k !== i; panels[k].classList.remove('is-from-right','is-from-left');
    });
    if (from !== i) panels[i].classList.add(i > from ? 'is-from-right' : 'is-from-left');
    root.querySelector('[data-tablist]').classList.toggle('is-second', i === 1);
    if (focus) tabs[i].focus();
  }
  tabs.forEach((t,i) => {
    t.addEventListener('click', () => select(i));
    t.addEventListener('keydown', e => {
      const keys = {ArrowRight:1-i, ArrowLeft:1-i, Home:0, End:1};
      if (e.key in keys) {e.preventDefault(); select(keys[e.key],true);}
    });
  });
  root.querySelectorAll('[data-go]').forEach(b => b.addEventListener('click', () => {
    if (busy) return;
    const i = b.dataset.go === 'signup' ? 1 : 0; select(i); panels[i].querySelector('input').focus();
  }));
  function check(field) {
    const input = field.querySelector('input');
    const form = input.form;
    const password = ['password','confirm'].includes(input.name);
    const value = password ? input.value : input.value.trim();
    let msg = '';
    if (input.required && !value) msg = 'Completa este campo.';
    else if (input.validity.typeMismatch) msg = 'Introduce un correo válido, como nombre@ejemplo.com.';
    else if (value.length > input.maxLength) msg = `Usa como máximo ${input.maxLength} caracteres.`;
    else if (form.dataset.form === 'signup' && input.name === 'password' && (Array.from(value).length < 8 || new TextEncoder().encode(value).length > 72)) msg = 'Usa al menos 8 caracteres y como máximo 72 bytes.';
    else if (input.name === 'confirm' && value !== form.elements.password.value) msg = 'Las contraseñas no coinciden.';
    field.querySelector('[data-error]').textContent = msg;
    field.classList.toggle('is-error', !!msg); input.setAttribute('aria-invalid', String(!!msg));
    return !msg;
  }
  root.querySelectorAll('[data-form]').forEach(form => {
    const fields = [...form.querySelectorAll('[data-field]')];
    fields.forEach(field => {
      field.addEventListener('focusout', e => {if (!field.contains(e.relatedTarget)) {field.dataset.touched='1';check(field);}});
      field.addEventListener('input', () => {if (field.dataset.touched) check(field);});
    });
    form.addEventListener('submit', async e => {
      e.preventDefault(); if (busy) return;
      const status = form.querySelector('[data-server]'); status.textContent = '';
      const bad = fields.filter(f => {f.dataset.touched='1'; return !check(f);});
      if (bad.length) {live.textContent='Revisa los campos indicados.';bad[0].querySelector('input').focus();return;}
      const payload = Object.fromEntries(new FormData(form));
      payload.csrf = document.querySelector('meta[name="csrf-token"]').content;
      const controls = [...root.querySelectorAll('button,input')];
      busy = true; controls.forEach(c => c.disabled = true); form.setAttribute('aria-busy','true');
      const submit = form.querySelector('[type="submit"]'); const label = submit.textContent;
      submit.textContent='Procesando…';
      let registered = false;
      try {
        const response = await fetch('api/auth/' + (form.dataset.form === 'signup' ? 'register' : 'login') + '.php', {
          method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload)
        });
        const result = await response.json();
        if (!response.ok) throw new Error(result.message || 'No se pudo completar la solicitud.');
        if (form.dataset.form === 'login') location.assign(result.data.redirect);
        else {
          registered=true; form.reset();
          const login=panels[0].querySelector('form'); login.elements.email.value=payload.email;
          login.querySelector('[data-server]').textContent=result.message;
        }
      } catch (error) {status.textContent=error.message || 'No se pudo conectar con el servidor.';}
      finally {
        busy=false; controls.forEach(c => c.disabled=false); form.removeAttribute('aria-busy');submit.textContent=label;
        if (registered) {select(0);panels[0].querySelector('[name="password"]').focus();}
      }
    });
  });
  root.querySelectorAll('[data-pw-toggle]').forEach(button => {
    const input=document.getElementById(button.getAttribute('aria-controls'));
    button.addEventListener('click', () => {
      const show=input.type === 'password';input.type=show?'text':'password';
      button.setAttribute('aria-pressed',String(show));button.setAttribute('aria-label',show?'Ocultar contraseña':'Mostrar contraseña');
    });
  });
})();
