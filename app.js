// ============================================================
// js/app.js — FlowTrack Frontend JavaScript
// Handles form validation, UI helpers, and symptom chips
// ============================================================

'use strict';

/* ── Auto-dismiss flash messages ────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    const flash = document.querySelector('.flash');
    if (flash) {
        setTimeout(() => {
            flash.style.transition = 'opacity .5s';
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 500);
        }, 3500);
    }

    // Symptom chip toggle
    document.querySelectorAll('.symptom-chip').forEach(chip => {
        const cb = chip.querySelector('input[type="checkbox"]');
        if (!cb) return;
        chip.classList.toggle('checked', cb.checked);
        chip.addEventListener('click', () => {
            chip.classList.toggle('checked', cb.checked);
        });
    });

    // Delete confirmation
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', e => {
            if (!confirm(btn.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    // Login form validation
    const loginForm = document.getElementById('loginForm');
    if (loginForm) validateLoginForm(loginForm);

    // Register form validation
    const registerForm = document.getElementById('registerForm');
    if (registerForm) validateRegisterForm(registerForm);

    // Cycle form validation
    const cycleForm = document.getElementById('cycleForm');
    if (cycleForm) validateCycleForm(cycleForm);

    // Symptom form
    const symptomForm = document.getElementById('symptomForm');
    if (symptomForm) validateSymptomForm(symptomForm);
});


/* ── Login Validation ────────────────────────────────────── */
function validateLoginForm(form) {
    form.addEventListener('submit', e => {
        clearErrors(form);
        let ok = true;

        const email = form.querySelector('#email');
        const password = form.querySelector('#password');

        if (!email.value.trim() || !isValidEmail(email.value)) {
            showError(email, 'Please enter a valid email address.');
            ok = false;
        }
        if (!password.value.trim()) {
            showError(password, 'Password is required.');
            ok = false;
        }
        if (!ok) e.preventDefault();
    });
}

/* ── Register Validation ─────────────────────────────────── */
function validateRegisterForm(form) {
    form.addEventListener('submit', e => {
        clearErrors(form);
        let ok = true;

        const name     = form.querySelector('#name');
        const email    = form.querySelector('#email');
        const password = form.querySelector('#password');
        const confirm  = form.querySelector('#confirm_password');

        if (!name.value.trim() || name.value.trim().length < 2) {
            showError(name, 'Name must be at least 2 characters.');
            ok = false;
        }
        if (!isValidEmail(email.value)) {
            showError(email, 'Enter a valid email address.');
            ok = false;
        }
        if (password.value.length < 8) {
            showError(password, 'Password must be at least 8 characters.');
            ok = false;
        }
        if (password.value !== confirm.value) {
            showError(confirm, 'Passwords do not match.');
            ok = false;
        }
        if (!ok) e.preventDefault();
    });
}

/* ── Cycle Form Validation ───────────────────────────────── */
function validateCycleForm(form) {
    form.addEventListener('submit', e => {
        clearErrors(form);
        let ok = true;

        const startDate    = form.querySelector('#start_date');
        const endDate      = form.querySelector('#end_date');
        const cycleLength  = form.querySelector('#cycle_length');
        const periodLength = form.querySelector('#period_length');

        if (!startDate.value) {
            showError(startDate, 'Start date is required.');
            ok = false;
        }
        if (endDate && endDate.value && startDate.value && endDate.value < startDate.value) {
            showError(endDate, 'End date cannot be before start date.');
            ok = false;
        }
        if (cycleLength && (cycleLength.value < 15 || cycleLength.value > 45)) {
            showError(cycleLength, 'Cycle length should be between 15 and 45 days.');
            ok = false;
        }
        if (periodLength && (periodLength.value < 1 || periodLength.value > 10)) {
            showError(periodLength, 'Period length should be between 1 and 10 days.');
            ok = false;
        }
        if (!ok) e.preventDefault();
    });
}

/* ── Symptom Form Validation ─────────────────────────────── */
function validateSymptomForm(form) {
    form.addEventListener('submit', e => {
        clearErrors(form);
        let ok = true;
        const logDate = form.querySelector('#log_date');
        const cycleId = form.querySelector('#cycle_id');
        if (!logDate.value) {
            showError(logDate, 'Please select a date.');
            ok = false;
        }
        if (cycleId && !cycleId.value) {
            showError(cycleId, 'Please select a cycle.');
            ok = false;
        }
        if (!ok) e.preventDefault();
    });
}

/* ── Helpers ─────────────────────────────────────────────── */
function showError(input, msg) {
    let span = input.parentElement.querySelector('.field-error');
    if (!span) {
        span = document.createElement('span');
        span.className = 'field-error';
        input.parentElement.appendChild(span);
    }
    span.textContent = msg;
    input.style.borderColor = '#c94560';
}

function clearErrors(form) {
    form.querySelectorAll('.field-error').forEach(el => el.remove());
    form.querySelectorAll('input, select, textarea').forEach(el => {
        el.style.borderColor = '';
    });
}

function isValidEmail(val) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val.trim());
}
