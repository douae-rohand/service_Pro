import api from './api';

/**
 * Service pour gérer les témoignages clients
 */
const testimonialService = {
    /**
     * Récupérer les témoignages pour la landing page
     */
    getTestimonials() {
        return api.get('testimonials');
    }
};

export default testimonialService;
