<?php
/**
 * Gestion des salles avec Vue.js
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Vérification de l'authentification
requireAdmin();

$admin = getLoggedAdmin();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Salles - SmartAccess UCB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        :root {
            --md-sys-color-primary: #6750A4;
            --md-sys-color-on-primary: #FFFFFF;
            --md-sys-color-primary-container: #EADDFF;
            --md-sys-color-on-primary-container: #21005D;
            --md-sys-color-surface: #FEF7FF;
            --md-sys-color-on-surface: #1D1B20;
            --md-sys-color-surface-variant: #E7E0EC;
            --md-sys-color-on-surface-variant: #49454F;
            --md-sys-color-outline: #79747E;
            --md-sys-color-error-container: #FFDAD6;
            --md-sys-color-on-error-container: #410002;
        }
        
        * {
            font-family: 'Roboto', sans-serif;
        }
        
        body { 
            background-color: var(--md-sys-color-surface);
            color: var(--md-sys-color-on-surface);
        }
        
        .navbar {
            background: var(--md-sys-color-primary);
            box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
        }
        
        .content-card {
            background: var(--md-sys-color-surface);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
            border: 1px solid var(--md-sys-color-outline);
            overflow: hidden;
        }
        
        .content-card .card-header {
            background: var(--md-sys-color-primary-container);
            color: var(--md-sys-color-on-primary-container);
            border: none;
            padding: 1rem 1.5rem;
        }
        
        .btn-primary {
            background: var(--md-sys-color-primary);
            color: var(--md-sys-color-on-primary);
            border: none;
            border-radius: 20px;
            padding: 10px 24px;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background: color-mix(in srgb, var(--md-sys-color-primary) 85%, black);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            transform: translateY(-1px);
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 16px;
            margin-bottom: 16px;
        }
        
        .alert-success {
            background: var(--md-sys-color-primary-container);
            color: var(--md-sys-color-on-primary-container);
        }
        
        .alert-danger {
            background: var(--md-sys-color-error-container);
            color: var(--md-sys-color-on-error-container);
        }
        
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid color-mix(in srgb, currentColor 30%, transparent);
            border-radius: 50%;
            border-top-color: currentColor;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .salle-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }
        .salle-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="../dashboard.php">
                <i class="bi bi-shield-lock-fill me-2"></i>
                SmartAccess UCB
            </a>
            
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i>Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="etudiants.php">
                            <i class="bi bi-people me-1"></i>Étudiants
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="salles.php">
                            <i class="bi bi-building me-1"></i>Salles
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="acces.php">
                            <i class="bi bi-key me-1"></i>Accès
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= htmlspecialchars($admin['prenom'] . ' ' . $admin['nom']) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4" id="app">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col">
                <h1 class="h3 mb-1">Gestion des Salles</h1>
                <p class="text-muted">Ajouter, modifier et gérer les salles du système</p>
            </div>
        </div>

        <!-- Formulaire d'ajout/modification -->
        <div class="row mb-4">
            <div class="col-lg-4">
                <div class="content-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <span class="material-icons me-2">add_business</span>
                            {{ editingSalle ? 'Modifier Salle' : 'Ajouter Salle' }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="saveSalle">
                            <div class="mb-3">
                                <label class="form-label">Nom de la salle</label>
                                <input type="text" 
                                       class="form-control" 
                                       v-model="form.nom_salle"
                                       placeholder="Ex: Salle Informatique A"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Localisation</label>
                                <input type="text" 
                                       class="form-control" 
                                       v-model="form.localisation"
                                       placeholder="Ex: Bâtiment Sciences - 1er étage">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Capacité</label>
                                <input type="number" 
                                       class="form-control" 
                                       v-model="form.capacite"
                                       min="1"
                                       placeholder="Ex: 30">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" 
                                          v-model="form.description"
                                          rows="3"
                                          placeholder="Description de la salle et de ses équipements..."></textarea>
                            </div>

                            <!-- Boutons d'action -->
                            <div class="d-grid gap-2">
                                <button type="submit" 
                                        class="btn btn-primary"
                                        :disabled="loading">
                                    <span v-if="loading" class="loading-spinner me-2"></span>
                                    <span v-else class="material-icons me-2">check</span>
                                    {{ editingSalle ? 'Mettre à jour' : 'Ajouter' }}
                                </button>
                                <button type="button" 
                                        class="btn btn-secondary"
                                        @click="resetForm"
                                        v-if="editingSalle">
                                    <span class="material-icons me-2">close</span>
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Liste des salles -->
            <div class="col-lg-8">
                <div class="content-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <span class="material-icons me-2">business</span>
                            Liste des Salles ({{ salles.length }})
                        </h5>
                        <button class="btn btn-light btn-sm" @click="loadSalles">
                            <span class="material-icons">refresh</span>
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Barre de recherche -->
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="material-icons">search</span>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       placeholder="Rechercher par nom ou localisation..."
                                       v-model="searchTerm"
                                       @input="searchSalles">
                            </div>
                        </div>

                        <!-- Grille des salles -->
                        <div v-if="loading && salles.length === 0" class="text-center py-4">
                            <div class="loading-spinner me-2"></div>
                            Chargement des salles...
                        </div>

                        <div v-else-if="filteredSalles.length === 0" class="text-center py-4 text-muted">
                            <i class="bi bi-building fs-1 mb-3"></i>
                            <p>Aucune salle trouvée.</p>
                        </div>

                        <div v-else class="row">
                            <div v-for="salle in filteredSalles" :key="salle.id" class="col-md-6 col-lg-4 mb-3">
                                <div class="card salle-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0">{{ salle.nom_salle }}</h6>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary" 
                                                        type="button" 
                                                        data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" 
                                                           href="#" 
                                                           @click.prevent="editSalle(salle)">
                                                            <i class="bi bi-pencil me-2"></i>Modifier
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" 
                                                           href="#" 
                                                           @click.prevent="deleteSalle(salle)">
                                                            <i class="bi bi-trash me-2"></i>Supprimer
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="bi bi-geo-alt me-1"></i>
                                                {{ salle.localisation || 'Localisation non spécifiée' }}
                                            </small>
                                        </div>
                                        
                                        <div class="mb-2" v-if="salle.capacite">
                                            <small class="text-muted">
                                                <i class="bi bi-people me-1"></i>
                                                Capacité: {{ salle.capacite }} personnes
                                            </small>
                                        </div>
                                        
                                        <p class="card-text small text-muted" v-if="salle.description">
                                            {{ salle.description.substring(0, 100) }}
                                            <span v-if="salle.description.length > 100">...</span>
                                        </p>
                                        
                                        <div class="mt-auto">
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Active
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertes -->
        <div v-if="alert.show" 
             :class="['alert', 'alert-' + alert.type, 'alert-dismissible']" 
             role="alert">
            <span :class="['material-icons', 'me-2']">
                {{ alert.type === 'success' ? 'check_circle' : 'warning' }}
            </span>
            {{ alert.message }}
            <button type="button" class="btn-close" @click="hideAlert" aria-label="Close"></button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script>
        const { createApp } = Vue;

        createApp({
            data() {
                return {
                    salles: [],
                    filteredSalles: [],
                    searchTerm: '',
                    loading: false,
                    editingSalle: null,
                    form: {
                        nom_salle: '',
                        localisation: '',
                        description: '',
                        capacite: ''
                    },
                    alert: {
                        show: false,
                        type: 'success',
                        message: ''
                    }
                }
            },
            mounted() {
                this.loadSalles();
            },
            methods: {
                async loadSalles() {
                    this.loading = true;
                    try {
                        const response = await fetch('../api/salles.php');
                        const data = await response.json();
                        
                        if (data.success) {
                            this.salles = data.salles;
                            this.filteredSalles = [...this.salles];
                        } else {
                            this.showAlert('error', 'Erreur lors du chargement des salles');
                        }
                    } catch (error) {
                        console.error('Erreur:', error);
                        this.showAlert('danger', 'Erreur de connexion');
                    } finally {
                        this.loading = false;
                    }
                },

                async saveSalle() {
                    this.loading = true;
                    try {
                        const url = '../api/salles.php';
                        const method = this.editingSalle ? 'PUT' : 'POST';
                        
                        const formData = { ...this.form };
                        if (this.editingSalle) {
                            formData.id = this.editingSalle.id;
                        }

                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(formData)
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.showAlert('success', this.editingSalle ? 'Salle modifiée avec succès' : 'Salle ajoutée avec succès');
                            this.resetForm();
                            this.loadSalles();
                        } else {
                            this.showAlert('danger', data.message || 'Erreur lors de l\'enregistrement');
                        }
                    } catch (error) {
                        console.error('Erreur:', error);
                        this.showAlert('danger', 'Erreur de connexion');
                    } finally {
                        this.loading = false;
                    }
                },

                editSalle(salle) {
                    this.editingSalle = salle;
                    this.form = { ...salle };
                },

                async deleteSalle(salle) {
                    if (!confirm(`Êtes-vous sûr de vouloir supprimer la salle "${salle.nom_salle}" ?`)) {
                        return;
                    }

                    try {
                        const response = await fetch('../api/salles.php', {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ id: salle.id })
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.showAlert('success', 'Salle supprimée avec succès');
                            this.loadSalles();
                        } else {
                            this.showAlert('danger', data.message || 'Erreur lors de la suppression');
                        }
                    } catch (error) {
                        console.error('Erreur:', error);
                        this.showAlert('danger', 'Erreur de connexion');
                    }
                },

                resetForm() {
                    this.editingSalle = null;
                    this.form = {
                        nom_salle: '',
                        localisation: '',
                        description: '',
                        capacite: ''
                    };
                },

                searchSalles() {
                    if (!this.searchTerm) {
                        this.filteredSalles = [...this.salles];
                        return;
                    }

                    const term = this.searchTerm.toLowerCase();
                    this.filteredSalles = this.salles.filter(salle => 
                        salle.nom_salle.toLowerCase().includes(term) ||
                        (salle.localisation && salle.localisation.toLowerCase().includes(term))
                    );
                },

                showAlert(type, message) {
                    this.alert = {
                        show: true,
                        type: type,
                        message: message
                    };
                    
                    setTimeout(() => {
                        this.hideAlert();
                    }, 5000);
                },

                hideAlert() {
                    this.alert.show = false;
                }
            }
        }).mount('#app');
    </script>
</body>
</html>