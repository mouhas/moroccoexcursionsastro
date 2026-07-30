// Shared site configuration: languages, navigation, contact.

export const LANGS = ['en', 'fr', 'es', 'it', 'pt-br'] as const;
export type Lang = (typeof LANGS)[number];

export const LANG_LABELS: Record<Lang, string> = {
  en: 'English',
  fr: 'Français',
  es: 'Español',
  it: 'Italiano',
  'pt-br': 'Português',
};

export const CONTACT = {
  phone: '+212 673 55 5408',
  phoneHref: 'tel:+212673555408',
  company: 'Morocco Excursions',
};

// Home path for a language
export function homeHref(lang: Lang): string {
  return lang === 'en' ? '/' : `/${lang}/`;
}

// Base prefix ('' for en, '/fr' for fr, etc.)
export function base(lang: Lang): string {
  return lang === 'en' ? '' : `/${lang}`;
}

// Primary navigation per language, using slugs that exist in the content.
export function getNav(lang: Lang): { label: string; href: string }[] {
  const b = base(lang);
  const map: Record<Lang, { label: string; href: string }[]> = {
    en: [
      { label: 'Home', href: '/' },
      { label: 'Tours', href: '/tours' },
      { label: 'Desert Tours', href: '/morocco-desert-tours' },
      { label: 'Car Rental', href: '/morocco-rental-cars' },
      { label: 'Reviews', href: '/reviews' },
      { label: 'Travel Agency', href: '/travel-agency' },
    ],
    fr: [
      { label: 'Accueil', href: '/fr/' },
      { label: 'Circuits', href: '/fr/tours' },
      { label: 'Désert', href: '/fr/circuit-desert-maroc' },
      { label: 'Location Voiture', href: '/fr/location-de-voitures' },
      { label: 'Avis', href: '/fr/reviews' },
      { label: 'Agence', href: '/fr/agence' },
    ],
    es: [
      { label: 'Inicio', href: '/es/' },
      { label: 'Tours', href: '/es/tours' },
      { label: 'Desierto', href: '/es/viaje-desierto-marruecos' },
      { label: 'Reseñas', href: '/es/reviews' },
      { label: 'Agencia', href: '/es/agencia' },
    ],
    it: [
      { label: 'Home', href: '/it/' },
      { label: 'Tour', href: '/it/tours' },
      { label: 'Deserto', href: '/it/tour-deserto-marocco' },
      { label: 'Noleggio Auto', href: '/it/noleggio-auto' },
      { label: 'Recensioni', href: '/it/reviews' },
      { label: 'Agenzia', href: '/it/agenzia' },
    ],
    'pt-br': [
      { label: 'Início', href: '/pt-br/' },
      { label: 'Tours', href: '/pt-br/tours' },
      { label: 'Deserto', href: '/pt-br/excursoes-deserto-marrocos' },
      { label: 'Aluguel de Carro', href: '/pt-br/aluguel-de-carro' },
      { label: 'Avaliações', href: '/pt-br/reviews' },
      { label: 'Agência', href: '/pt-br/agencia' },
    ],
  };
  return map[lang] ?? map.en;
}

// Real testimonials from the company's homepage (used as a fallback on tours
// that don't have their own scraped review list — never invented).
export const GENERAL_REVIEWS: Record<Lang, { name: string; place: string; text: string }[]> = {
  en: [
    { name: 'Nora L.', place: 'United States', text: 'Our tour guide Ali was so kind, and I can’t say enough good things about him and his knowledge about Morocco.' },
    { name: 'Michael T.', place: 'United Kingdom', text: 'It was one of my most memorable vacations, and I would highly recommend this agency to everyone.' },
    { name: 'Olivier P.', place: 'Australia', text: 'Ali was gentle, patient, and flexible. Thank you Morocco Excursions for this amazing Morocco tour.' },
  ],
  fr: [
    { name: 'Josephine O.', place: 'Canada', text: 'Notre guide Ali était si gentil, et je ne peux pas dire assez de bien de lui et de ses connaissances sur le Maroc.' },
    { name: 'Gabriel Y.', place: 'France', text: 'Ce fut l’une de mes vacances les plus mémorables, et je recommanderais vivement cette agence à tout le monde.' },
    { name: 'Jules B.', place: 'France', text: 'Ali était doux, patient et flexible. Merci à Morocco Excursions pour cet incroyable voyage au Maroc.' },
  ],
  es: [
    { name: 'Fernanda P.', place: 'Argentina', text: 'Nuestro guía turístico, Ali, fue muy amable y no tengo palabras para describirlo y sus conocimientos sobre Marruecos.' },
    { name: 'Miguel O.', place: 'España', text: 'Fueron unas de mis vacaciones más memorables y recomendaría esta agencia a todo el mundo.' },
    { name: 'Oscar L.', place: 'Colombia', text: 'Ali fue amable, paciente y flexible. Gracias a Morocco Excursions por este increíble tour por Marruecos.' },
  ],
  it: [
    { name: 'Laura N.', place: '', text: 'La nostra guida turistica Ali è stata così gentile e non posso dire abbastanza cose positive su di lui e sulla sua conoscenza del Marocco.' },
    { name: 'Marco C.', place: '', text: 'È stata una delle mie vacanze più memorabili e consiglierei vivamente questa agenzia a tutti.' },
    { name: 'Giaco K.', place: '', text: 'Ali è stato gentile, paziente e flessibile. Grazie Morocco Excursions per questo fantastico tour in Marocco.' },
  ],
  'pt-br': [
    { name: 'Elena', place: 'Brasil', text: 'Nosso guia turístico Ali foi muito gentil, e não posso dizer coisas boas o suficiente sobre ele e seu conhecimento sobre o Marrocos.' },
    { name: 'Mauro J.', place: 'Portugal', text: 'Foi uma das minhas férias mais memoráveis, e eu recomendo fortemente esta agência a todos.' },
    { name: 'Bernardo K.', place: 'Brasil', text: 'Ali foi gentil, paciente e flexível. Obrigado Morocco Excursions por este tour incrível no Marrocos.' },
  ],
};

export const UI = {
  en: {
    book: 'Book a Trip', viewAll: 'View All Tours', from: 'from', bookNow: 'Book This Trip', planTrip: 'Plan a Custom Trip',
    readReviews: 'Read All Reviews', similar: 'You Might Also Like', allTours: 'All Tours & Excursions', perDay: '/ day',
    tabOverview: 'Overview', tabItinerary: 'Itinerary', tabIncluded: "What's Included", tabReviews: 'Reviews', tabFaq: 'FAQ',
    highlights: 'Highlights', pricing: 'Pricing', included: 'Included', excluded: 'Not Included', dayItinerary: 'Day-by-Day Itinerary',
    goodToKnow: 'Good to Know', customerReviews: 'Customer Reviews', frequentlyAsked: 'Frequently Asked Questions',
    checkAvailability: 'Check Availability', requestBooking: 'Request to Book', fullName: 'Full Name', email: 'Email',
    phone: 'Phone / WhatsApp', preferredDate: 'Preferred Date', travelers: 'Travelers', message: 'Message (optional)',
    sendEmail: 'Send Booking Request', chatWhatsapp: 'Chat on WhatsApp', bookSuccess: "Thanks! We've opened your email app — send it and our team will reply within a few hours.",
    freeCancellation: 'Free Cancellation', private: 'Private Tour', reviewsLabel: 'reviews', notRated: 'Not yet rated',
    category: 'Category', features: 'Features', rentalRequest: 'Rental Request', pickupDate: 'Pick-up Date', returnDate: 'Return Date',
    messagePlaceholder: "Tell us your dates, group size, or anything else we should know.",
    tabMap: 'Map', tourMap: 'Tour Route Map', tourCode: 'Tour Code', duration: 'Duration', tourType: 'Tour Type',
    noReviewsYet: "This tour doesn't have dedicated reviews yet.", generalReviewsNote: 'Here’s what other travellers say about Morocco Excursions:',
    prevTour: 'Previous Tour', nextTour: 'Next Tour', payDeposit: 'Pay 10% Deposit', payFull: 'Pay in Full',
    payWithPaypal: 'Pay securely with PayPal', paypalSandboxNote: 'Test mode — no real charge (PayPal sandbox)',
    orRequestInstead: 'Prefer to just ask a question first?', paymentSuccess: 'Payment received! We’ll email your booking confirmation shortly.',
    totalDue: 'Total due now', perPerson: 'per person',
    accommodation: 'Accommodation', accStandard: 'Standard', accSuperior: 'Superior (+€50/person/night)',
    extras: 'Desert Extras', extraQuadSingle: 'Quad Bike/Person', extraQuadSingleNote: '€50 / person',
    extraQuadShared: 'Quad Bike/2 People', extraQuadSharedNote: '€75 / pair',
    extraBuggy: 'Buggy/2 People', extraBuggyNote: '€130 / buggy',
    contactForQuote: 'For groups this size, contact us for a custom quote.',
    ratingHotels: 'Hotels', ratingGuides: 'Guides', ratingTransport: 'Transport', ratingActivities: 'Activities', overallRating: 'Overall Rating',
  },
  fr: {
    book: 'Réserver', viewAll: 'Voir tous les circuits', from: 'à partir de', bookNow: 'Réserver ce circuit', planTrip: 'Circuit sur mesure',
    readReviews: 'Tous les avis', similar: 'Vous aimerez aussi', allTours: 'Tous les circuits', perDay: '/ jour',
    tabOverview: 'Aperçu', tabItinerary: 'Itinéraire', tabIncluded: 'Inclus', tabReviews: 'Avis', tabFaq: 'FAQ',
    highlights: 'Points forts', pricing: 'Tarifs', included: 'Inclus', excluded: 'Non inclus', dayItinerary: 'Itinéraire jour par jour',
    goodToKnow: 'Bon à savoir', customerReviews: 'Avis clients', frequentlyAsked: 'Questions fréquentes',
    checkAvailability: 'Vérifier la disponibilité', requestBooking: 'Demander une réservation', fullName: 'Nom complet', email: 'E-mail',
    phone: 'Téléphone / WhatsApp', preferredDate: 'Date souhaitée', travelers: 'Voyageurs', message: 'Message (facultatif)',
    sendEmail: 'Envoyer la demande', chatWhatsapp: 'Discuter sur WhatsApp', bookSuccess: 'Merci ! Votre application e-mail est ouverte — envoyez-la et notre équipe répondra sous quelques heures.',
    freeCancellation: 'Annulation gratuite', private: 'Circuit privé', reviewsLabel: 'avis', notRated: 'Pas encore noté',
    category: 'Catégorie', features: 'Caractéristiques', rentalRequest: 'Demande de location', pickupDate: 'Date de prise en charge', returnDate: 'Date de retour',
    messagePlaceholder: 'Indiquez vos dates, la taille du groupe ou toute autre information utile.',
    tabMap: 'Carte', tourMap: 'Carte de l’itinéraire', tourCode: 'Code circuit', duration: 'Durée', tourType: 'Type de circuit',
    noReviewsYet: "Ce circuit n'a pas encore d'avis spécifiques.", generalReviewsNote: 'Voici ce que d’autres voyageurs disent de Morocco Excursions :',
    prevTour: 'Circuit précédent', nextTour: 'Circuit suivant', payDeposit: 'Payer 10 % d’acompte', payFull: 'Payer en totalité',
    payWithPaypal: 'Payer en toute sécurité avec PayPal', paypalSandboxNote: 'Mode test — aucun paiement réel (PayPal sandbox)',
    orRequestInstead: 'Vous préférez juste poser une question ?', paymentSuccess: 'Paiement reçu ! Vous recevrez votre confirmation par e-mail sous peu.',
    totalDue: 'Total à payer maintenant', perPerson: 'par personne',
    accommodation: 'Hébergement', accStandard: 'Standard', accSuperior: 'Supérieur (+50 €/personne/nuit)',
    extras: 'Extras désert', extraQuadSingle: 'Quad/Personne', extraQuadSingleNote: '50 € / personne',
    extraQuadShared: 'Quad/2 Personnes', extraQuadSharedNote: '75 € / paire',
    extraBuggy: 'Buggy/2 Personnes', extraBuggyNote: '130 € / buggy',
    contactForQuote: 'Pour ce nombre de personnes, contactez-nous pour un devis sur mesure.',
    ratingHotels: 'Hôtels', ratingGuides: 'Guides', ratingTransport: 'Transport', ratingActivities: 'Activités', overallRating: 'Note Globale',
  },
  es: {
    book: 'Reservar', viewAll: 'Ver todos los tours', from: 'desde', bookNow: 'Reservar este tour', planTrip: 'Viaje personalizado',
    readReviews: 'Ver todas las reseñas', similar: 'También te puede gustar', allTours: 'Todos los tours', perDay: '/ día',
    tabOverview: 'Resumen', tabItinerary: 'Itinerario', tabIncluded: 'Incluye', tabReviews: 'Reseñas', tabFaq: 'FAQ',
    highlights: 'Lo más destacado', pricing: 'Precios', included: 'Incluido', excluded: 'No incluido', dayItinerary: 'Itinerario día a día',
    goodToKnow: 'Bueno saber', customerReviews: 'Opiniones de clientes', frequentlyAsked: 'Preguntas frecuentes',
    checkAvailability: 'Comprobar disponibilidad', requestBooking: 'Solicitar reserva', fullName: 'Nombre completo', email: 'Correo electrónico',
    phone: 'Teléfono / WhatsApp', preferredDate: 'Fecha preferida', travelers: 'Viajeros', message: 'Mensaje (opcional)',
    sendEmail: 'Enviar solicitud', chatWhatsapp: 'Chatear por WhatsApp', bookSuccess: 'Gracias. Hemos abierto tu app de correo — envíalo y nuestro equipo responderá en unas horas.',
    freeCancellation: 'Cancelación gratuita', private: 'Tour privado', reviewsLabel: 'reseñas', notRated: 'Sin valorar',
    category: 'Categoría', features: 'Características', rentalRequest: 'Solicitud de alquiler', pickupDate: 'Fecha de recogida', returnDate: 'Fecha de devolución',
    messagePlaceholder: 'Cuéntanos tus fechas, el tamaño del grupo o cualquier otra cosa que debamos saber.',
    tabMap: 'Mapa', tourMap: 'Mapa de la ruta', tourCode: 'Código del tour', duration: 'Duración', tourType: 'Tipo de tour',
    noReviewsYet: 'Este tour todavía no tiene reseñas propias.', generalReviewsNote: 'Esto es lo que dicen otros viajeros sobre Morocco Excursions:',
    prevTour: 'Tour anterior', nextTour: 'Siguiente tour', payDeposit: 'Pagar 10% de depósito', payFull: 'Pagar el total',
    payWithPaypal: 'Paga de forma segura con PayPal', paypalSandboxNote: 'Modo de prueba — sin cargo real (PayPal sandbox)',
    orRequestInstead: '¿Prefieres solo hacer una pregunta primero?', paymentSuccess: '¡Pago recibido! Te enviaremos la confirmación por correo en breve.',
    totalDue: 'Total a pagar ahora', perPerson: 'por persona',
    accommodation: 'Alojamiento', accStandard: 'Estándar', accSuperior: 'Superior (+€50/persona/noche)',
    extras: 'Extras del desierto', extraQuadSingle: 'Quad/Persona', extraQuadSingleNote: '€50 / persona',
    extraQuadShared: 'Quad/2 Personas', extraQuadSharedNote: '€75 / pareja',
    extraBuggy: 'Buggy/2 Personas', extraBuggyNote: '€130 / buggy',
    contactForQuote: 'Para grupos de este tamaño, contáctanos para una cotización personalizada.',
    ratingHotels: 'Hoteles', ratingGuides: 'Guías', ratingTransport: 'Transporte', ratingActivities: 'Actividades', overallRating: 'Valoración General',
  },
  it: {
    book: 'Prenota', viewAll: 'Vedi tutti i tour', from: 'da', bookNow: 'Prenota questo tour', planTrip: 'Viaggio su misura',
    readReviews: 'Tutte le recensioni', similar: 'Potrebbe piacerti anche', allTours: 'Tutti i tour', perDay: '/ giorno',
    tabOverview: 'Panoramica', tabItinerary: 'Itinerario', tabIncluded: 'Incluso', tabReviews: 'Recensioni', tabFaq: 'FAQ',
    highlights: 'Punti salienti', pricing: 'Prezzi', included: 'Incluso', excluded: 'Non incluso', dayItinerary: 'Itinerario giorno per giorno',
    goodToKnow: 'Da sapere', customerReviews: 'Recensioni dei clienti', frequentlyAsked: 'Domande frequenti',
    checkAvailability: 'Verifica disponibilità', requestBooking: 'Richiedi prenotazione', fullName: 'Nome completo', email: 'E-mail',
    phone: 'Telefono / WhatsApp', preferredDate: 'Data preferita', travelers: 'Viaggiatori', message: 'Messaggio (facoltativo)',
    sendEmail: 'Invia richiesta', chatWhatsapp: 'Chatta su WhatsApp', bookSuccess: 'Grazie! Abbiamo aperto la tua app email — inviala e il nostro team risponderà entro poche ore.',
    freeCancellation: 'Cancellazione gratuita', private: 'Tour privato', reviewsLabel: 'recensioni', notRated: 'Non ancora valutato',
    category: 'Categoria', features: 'Caratteristiche', rentalRequest: 'Richiesta di noleggio', pickupDate: 'Data di ritiro', returnDate: 'Data di riconsegna',
    messagePlaceholder: 'Indicaci le tue date, la dimensione del gruppo o altro che dovremmo sapere.',
    tabMap: 'Mappa', tourMap: 'Mappa del percorso', tourCode: 'Codice tour', duration: 'Durata', tourType: 'Tipo di tour',
    noReviewsYet: 'Questo tour non ha ancora recensioni proprie.', generalReviewsNote: 'Ecco cosa dicono altri viaggiatori di Morocco Excursions:',
    prevTour: 'Tour precedente', nextTour: 'Tour successivo', payDeposit: 'Paga il 10% di acconto', payFull: 'Paga per intero',
    payWithPaypal: 'Paga in sicurezza con PayPal', paypalSandboxNote: 'Modalità test — nessun addebito reale (PayPal sandbox)',
    orRequestInstead: 'Preferisci prima solo fare una domanda?', paymentSuccess: 'Pagamento ricevuto! Riceverai la conferma via email a breve.',
    totalDue: 'Totale da pagare ora', perPerson: 'a persona',
    accommodation: 'Alloggio', accStandard: 'Standard', accSuperior: 'Superiore (+€50/persona/notte)',
    extras: 'Extra deserto', extraQuadSingle: 'Quad/Persona', extraQuadSingleNote: '€50 / persona',
    extraQuadShared: 'Quad/2 Persone', extraQuadSharedNote: '€75 / coppia',
    extraBuggy: 'Buggy/2 Persone', extraBuggyNote: '€130 / buggy',
    contactForQuote: 'Per gruppi di queste dimensioni, contattaci per un preventivo personalizzato.',
    ratingHotels: 'Hotel', ratingGuides: 'Guide', ratingTransport: 'Trasporto', ratingActivities: 'Attività', overallRating: 'Valutazione Complessiva',
  },
  'pt-br': {
    book: 'Reservar', viewAll: 'Ver todos os tours', from: 'a partir de', bookNow: 'Reservar este tour', planTrip: 'Viagem personalizada',
    readReviews: 'Ver todas as avaliações', similar: 'Você também pode gostar', allTours: 'Todos os tours', perDay: '/ dia',
    tabOverview: 'Visão Geral', tabItinerary: 'Roteiro', tabIncluded: 'O que está incluso', tabReviews: 'Avaliações', tabFaq: 'FAQ',
    highlights: 'Destaques', pricing: 'Preços', included: 'Incluso', excluded: 'Não incluso', dayItinerary: 'Roteiro dia a dia',
    goodToKnow: 'Bom saber', customerReviews: 'Avaliações de clientes', frequentlyAsked: 'Perguntas frequentes',
    checkAvailability: 'Verificar disponibilidade', requestBooking: 'Solicitar reserva', fullName: 'Nome completo', email: 'E-mail',
    phone: 'Telefone / WhatsApp', preferredDate: 'Data preferida', travelers: 'Viajantes', message: 'Mensagem (opcional)',
    sendEmail: 'Enviar pedido de reserva', chatWhatsapp: 'Conversar no WhatsApp', bookSuccess: 'Obrigado! Abrimos seu app de e-mail — envie e nossa equipe responderá em poucas horas.',
    freeCancellation: 'Cancelamento grátis', private: 'Tour privado', reviewsLabel: 'avaliações', notRated: 'Ainda sem avaliação',
    category: 'Categoria', features: 'Recursos', rentalRequest: 'Pedido de aluguel', pickupDate: 'Data de retirada', returnDate: 'Data de devolução',
    messagePlaceholder: 'Conte-nos suas datas, tamanho do grupo ou qualquer outra coisa que devamos saber.',
    tabMap: 'Mapa', tourMap: 'Mapa da rota', tourCode: 'Código do tour', duration: 'Duração', tourType: 'Tipo de tour',
    noReviewsYet: 'Este tour ainda não tem avaliações próprias.', generalReviewsNote: 'Veja o que outros viajantes dizem sobre a Morocco Excursions:',
    prevTour: 'Tour anterior', nextTour: 'Próximo tour', payDeposit: 'Pagar 10% de sinal', payFull: 'Pagar o valor total',
    payWithPaypal: 'Pague com segurança via PayPal', paypalSandboxNote: 'Modo de teste — sem cobrança real (PayPal sandbox)',
    orRequestInstead: 'Prefere apenas fazer uma pergunta primeiro?', paymentSuccess: 'Pagamento recebido! Enviaremos a confirmação por e-mail em breve.',
    totalDue: 'Total a pagar agora', perPerson: 'por pessoa',
    accommodation: 'Acomodação', accStandard: 'Padrão', accSuperior: 'Superior (+€50/pessoa/noite)',
    extras: 'Extras do deserto', extraQuadSingle: 'Quadriciclo/Pessoa', extraQuadSingleNote: '€50 / pessoa',
    extraQuadShared: 'Quadriciclo/2 Pessoas', extraQuadSharedNote: '€75 / dupla',
    extraBuggy: 'Buggy/2 Pessoas', extraBuggyNote: '€130 / buggy',
    contactForQuote: 'Para grupos deste tamanho, entre em contato para um orçamento personalizado.',
    ratingHotels: 'Hotéis', ratingGuides: 'Guias', ratingTransport: 'Transporte', ratingActivities: 'Atividades', overallRating: 'Avaliação Geral',
  },
} as const;
