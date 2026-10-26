// cypress/e2e/admin/front_settings.cy.js

describe('Admin – Front Settings (CMS)', () => {
  beforeEach(() => {
    cy.loginAsAdmin();
  });

  it('affiche la gestion du menu d\'en-tête (Header Menu Manager)', () => {
    cy.visit('/header-menu-manager');
    cy.url().should('include', '/header-menu-manager');
    cy.get('body').should('be.visible');
    cy.screenshot('admin_front_header_menu_manager');
  });

  it('affiche la configuration de la page d\'accueil front-end', () => {
    cy.visit('/admin-home-page');
    cy.url().should('include', '/admin-home-page');
    cy.get('form, .card').should('exist');
    cy.screenshot('admin_front_home_page');
  });

  it('affiche la gestion de l\'en-tête des actualités', () => {
    cy.visit('/news-heading-update');
    cy.url().should('include', '/news-heading-update');
    cy.get('body').should('be.visible');
    cy.screenshot('admin_front_news_heading');
  });

  it('affiche les catégories d\'actualités', () => {
    cy.visit('/news-category');
    cy.url().should('include', '/news-category');
    cy.get('table, .card').should('exist');
    cy.screenshot('admin_front_news_category');
  });

  it('affiche la liste des actualités', () => {
    cy.visit('/news');
    cy.url().should('include', '/news');
    cy.get('table, .card').should('exist');
    cy.screenshot('admin_front_news_list');
  });

  it('affiche les catégories de cours', () => {
    cy.visit('/course-category');
    cy.url().should('include', '/course-category');
    cy.get('table, .card').should('exist');
    cy.screenshot('admin_front_course_category');
  });

  it('affiche la liste des cours', () => {
    cy.visit('/course-list');
    cy.url().should('include', '/course-list');
    cy.get('table, .card').should('exist');
    cy.screenshot('admin_front_course_list');
  });

  it('affiche la liste des témoignages', () => {
    cy.visit('/testimonial');
    cy.url().should('include', '/testimonial');
    cy.get('table, .card').should('exist');
    cy.screenshot('admin_front_testimonial');
  });

  it('affiche les paramètres de la page de contact', () => {
    cy.visit('/contact-page');
    cy.url().should('include', '/contact-page');
    cy.get('body').should('be.visible');
    cy.screenshot('admin_front_contact_page');
  });

  it('affiche les messages de contact reçus', () => {
    cy.visit('/contact-message');
    cy.url().should('include', '/contact-message');
    cy.get('body').should('be.visible');
    cy.screenshot('admin_front_contact_message');
  });

  it('affiche les paramètres de la page À propos', () => {
    cy.visit('/about-page');
    cy.url().should('include', '/about-page');
    cy.get('body').should('be.visible');
    cy.screenshot('admin_front_about_page');
  });

  it('affiche les paramètres des réseaux sociaux', () => {
    cy.visit('/social-media');
    cy.url().should('include', '/social-media');
    cy.get('table, .card').should('exist');
    cy.screenshot('admin_front_social_media');
  });

  it('affiche la liste des sliders d\'accueil', () => {
    cy.visit('/home-slider');
    cy.url().should('include', '/home-slider');
    cy.get('table, .card').should('exist');
    cy.screenshot('admin_front_home_slider');
  });

  it('affiche la liste des enseignants experts', () => {
    cy.visit('/expert-teacher');
    cy.url().should('include', '/expert-teacher');
    cy.get('table, .card').should('exist');
    cy.screenshot('admin_front_expert_teacher');
  });

  it('affiche la galerie photo front-end', () => {
    cy.visit('/photo-gallery');
    cy.url().should('include', '/photo-gallery');
    cy.get('table, .card').should('exist');
    cy.screenshot('admin_front_photo_gallery');
  });

  it('affiche la galerie vidéo front-end', () => {
    cy.visit('/video-gallery');
    cy.url().should('include', '/video-gallery');
    cy.get('table, .card').should('exist');
    cy.screenshot('admin_front_video_gallery');
  });

  it('affiche les résultats front-end', () => {
    cy.visit('/front-result');
    cy.url().should('include', '/front-result');
    cy.get('table, .card').should('exist');
    cy.screenshot('admin_front_result');
  });

  it('affiche la routine de classe front-end', () => {
    cy.visit('/front-class-routine');
    cy.url().should('include', '/front-class-routine');
    cy.get('table, .card').should('exist');
    cy.screenshot('admin_front_class_routine');
  });

  it('affiche la routine d\'examen front-end', () => {
    cy.visit('/front-exam-routine');
    cy.url().should('include', '/front-exam-routine');
    cy.get('table, .card').should('exist');
    cy.screenshot('admin_front_exam_routine');
  });

  it('affiche le calendrier académique front-end', () => {
    cy.visit('/front-academic-calendar');
    cy.url().should('include', '/front-academic-calendar');
    cy.get('table, .card').should('exist');
    cy.screenshot('admin_front_academic_calendar');
  });

  it('affiche les curseurs de discours (speech slider)', () => {
    cy.visit('/speech-slider');
    cy.url().should('include', '/speech-slider');
    cy.get('table, .card').should('exist');
    cy.screenshot('admin_front_speech_slider');
  });

  it('affiche la liste des donateurs', () => {
    cy.visit('/donor');
    cy.url().should('include', '/donor');
    cy.get('table, .card').should('exist');
    cy.screenshot('admin_front_donor');
  });

  it('affiche les formulaires à télécharger', () => {
    cy.visit('/form-download');
    cy.url().should('include', '/form-download');
    cy.get('table, .card').should('exist');
    cy.screenshot('admin_front_form_download');
  });

  it('affiche la liste des pages dynamiques', () => {
    cy.visit('/page-list');
    cy.url().should('include', '/page-list');
    cy.get('table, .card').should('exist');
    cy.screenshot('admin_front_page_list');
  });

  it('affiche la gestion des liens personnalisés (footer)', () => {
    cy.visit('/custom-links');
    cy.url().should('include', '/custom-links');
    cy.get('table, .card, form').should('exist');
    cy.screenshot('admin_front_custom_links');
  });
});
