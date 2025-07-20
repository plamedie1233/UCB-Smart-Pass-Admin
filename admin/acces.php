<?php
/**
 * Gestion des accès avec Vue.js
 * Attribution individuelle et groupée d'accès
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
    <title>Gestion des Accès - SmartAccess UCB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .content-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .content-card .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1rem 1.5rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .tab-content {
            padding: 1.5rem;
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
                        <a class="nav-link" href="salles.php">
                            <i class="bi bi-building me-1"></i>Salles
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="acces.php">
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
                <h1 class="h3 mb-1">Gestion des Accès</h1>
                <p class="text-muted">Attribution individuelle et groupée d'accès aux salles</p>
            </div>
        </div>

        <!-- Onglets -->
        <div class="row">
            <div class="col">
                <div class="content-card">
                    <div class="card-header">
                        <ul class="nav nav-pills card-header-pills" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" 
                                        id="individual-tab" 
                                        data-bs-toggle="pill" 
                                        data-bs-target="#individual" 
                                        type="button" 
                                        role="tab">
                                    <i class="bi bi-person me-2"></i>
                                    Attribution Individuelle
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" 
                                        id="group-tab" 
                                        data-bs-toggle="pill" 
                                        data-bs-target="#group" 
                                        type="button" 
                                        role="tab">
                                    <i class="bi bi-people me-2"></i>
                                    Attribution Groupée
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" 
                                        id="list-tab" 
                                        data-bs-toggle="pill" 
                                        data-bs-target="#list" 
                                        type="button" 
                                        role="tab"
                                        @click="loadAutorisations">
                                    <i class="bi bi-list me-2"></i>
                                    Liste des Autorisations
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content">
                        <!-- Attribution Individuelle -->
                        <div class="tab-pane fade show active" id="individual" role="tabpanel">
                            <div class="row">
                                <div class="col-lg-6">
                                    <h5 class="mb-3">
                                        <i class="bi bi-person-plus me-2"></i>
                                        Attribuer un accès individuel
                                    </h5>
                                    
                                    <form @submit.prevent="saveIndividualAccess">
                                        <div class="mb-3">
                                            <label class="form-label">Étudiant</label>
                                            <select class="form-select" v-model="individualForm.etudiant_id" required>
                                                <option value="">Sélectionner un étudiant</option>
                                                <option v-for="etudiant in etudiants" 
                                                        :key="etudiant.id" 
                                                        :value="etudiant.id">
                                                    {{ etudiant.matricule }} - {{ etudiant.nom }} {{ etudiant.prenom }}
                                                </option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Salle</label>
                                            <select class="form-select" v-model="individualForm.salle_id" required>
                                                <option value="">Sélectionner une salle</option>
                                                <option v-for="salle in salles" 
                                                        :key="salle.id" 
                                                        :value="salle.id">
                                                    {{ salle.nom_salle }} - {{ salle.localisation }}
                                                </option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Date de début</label>
                                                    <input type="datetime-local" 
                                                           class="form-control" 
                                                           v-model="individualForm.date_debut"
                                                           required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Date de fin</label>
                                                    <input type="datetime-local" 
                                                           class="form-control" 
                                                           v-model="individualForm.date_fin"
                                                           required>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="submit" 
                                                class="btn btn-primary"
                                                :disabled="loading">
                                            <span v-if="loading" class="loading-spinner me-2"></span>
                                            <i v-else class="bi bi-check-lg me-2"></i>
                                            Attribuer l'accès
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Attribution Groupée -->
                        <div class="tab-pane fade" id="group" role="tabpanel">
                            <div class="row">
                                <div class="col-lg-8">
                                    <h5 class="mb-3">
                                        <i class="bi bi-people-fill me-2"></i>
                                        Attribution groupée par faculté/promotion
                                    </h5>
                                    
                                    <form @submit.prevent="saveGroupAccess">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Faculté</label>
                                                    <select class="form-select" 
                                                            v-model="groupForm.faculte" 
                                                            @change="loadPromotions"
                                                            required>
                                                        <option value="">Sélectionner une faculté</option>
                                                        <option v-for="faculte in facultes" 
                                                                :key="faculte.id" 
                                                                :value="faculte.name">
                                                            {{ faculte.name }}
                                                        </option>
                                                    </select>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-primary mt-2"
                                                            @click="loadFacultesFromUCB"
                                                            :disabled="loadingFacultes">
                                                        <span v-if="loadingFacultes" class="loading-spinner me-1"></span>
                                                        <i v-else class="bi bi-download me-1"></i>
                                                        Charger depuis UCB
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Promotion</label>
                                                    <select class="form-select" 
                                                            v-model="groupForm.promotion" 
                                                            :disabled="!groupForm.faculte"
                                                            required>
                                                        <option value="">Sélectionner une promotion</option>
                                                        <option v-for="promotion in filteredPromotions" 
                                                                :key="promotion.id" 
                                                                :value="promotion.name">
                                                            {{ promotion.name }}
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Salle</label>
                                            <select class="form-select" v-model="groupForm.salle_id" required>
                                                <option value="">Sélectionner une salle</option>
                                                <option v-for="salle in salles" 
                                                        :key="salle.id" 
                                                        :value="salle.id">
                                                    {{ salle.nom_salle }} - {{ salle.localisation }}
                                                </option>
                                            </select>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Date de début</label>
                                                    <input type="datetime-local" 
                                                           class="form-control" 
                                                           v-model="groupForm.date_debut"
                                                           required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Date de fin</label>
                                                    <input type="datetime-local" 
                                                           class="form-control" 
                                                           v-model="groupForm.date_fin"
                                                           required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Aperçu des étudiants concernés -->
                                        <div v-if="groupForm.faculte && groupForm.promotion" class="mb-3">
                                            <div class="alert alert-info">
                                                <i class="bi bi-info-circle me-2"></i>
                                                <strong>Étudiants concernés:</strong> 
                                                {{ getStudentsCount() }} étudiant(s) de la faculté "{{ groupForm.faculte }}" 
                                                promotion "{{ groupForm.promotion }}"
                                            </div>
                                        </div>

                                        <button type="submit" 
                                                class="btn btn-primary"
                                                :disabled="loading">
                                            <span v-if="loading" class="loading-spinner me-2"></span>
                                            <i v-else class="bi bi-people-fill me-2"></i>
                                            Attribuer l'accès groupé
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Liste des Autorisations -->
                        <div class="tab-pane fade" id="list" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">
                                    <i class="bi bi-list-ul me-2"></i>
                                    Autorisations Actives ({{ autorisations.length }})
                                </h5>
                                <button class="btn btn-outline-primary btn-sm" @click="loadAutorisations">
                                    <i class="bi bi-arrow-clockwise"></i>
                                    Actualiser
                                </button>
                            </div>

                            <!-- Barre de recherche -->
                            <div class="mb-3">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           placeholder="Rechercher par étudiant, matricule ou salle..."
                                           v-model="searchAutorisations"
                                           @input="filterAutorisations">
                                </div>
                            </div>

                            <!-- Tableau des autorisations -->
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Étudiant</th>
                                            <th>Matricule</th>
                                            <th>Salle</th>
                                            <th>Période</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="loading && autorisations.length === 0">
                                            <td colspan="6" class="text-center py-4">
                                                <div class="loading-spinner me-2"></div>
                                                Chargement des autorisations...
                                            </td>
                                        </tr>
                                        <tr v-else-if="filteredAutorisations.length === 0">
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                <i class="bi bi-inbox fs-1 mb-3"></i>
                                                <p>Aucune autorisation trouvée.</p>
                                            </td>
                                        </tr>
                                        <tr v-for="auth in filteredAutorisations" :key="auth.id">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center me-2">
                                                        <i class="bi bi-person"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold">{{ auth.nom }} {{ auth.prenom }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ auth.matricule }}</span>
                                            </td>
                                            <td>{{ auth.nom_salle }}</td>
                                            <td>
                                                <small>
                                                    Du {{ formatDate(auth.date_debut) }}<br>
                                                    Au {{ formatDate(auth.date_fin) }}
                                                </small>
                                            </td>
                                            <td>
                                                <span :class="['badge', auth.actif ? 'bg-success' : 'bg-danger']">
                                                    {{ auth.actif ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        @click="revokeAccess(auth)"
                                                        title="Révoquer l'accès">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertes -->
        <transition name="fade">
            <div v-if="alert.show" 
                 :class="['alert', 'alert-' + alert.type, 'alert-dismissible', 'fade', 'show']" 
                 role="alert">
                <i :class="['bi', alert.type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle', 'me-2']"></i>
                {{ alert.message }}
                <button type="button" class="btn-close" @click="hideAlert"></button>
            </div>
        </transition>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script>
        const { createApp } = Vue;

        createApp({
            data() {
                return {
                    etudiants: [],
                    salles: [],
                    autorisations: [],
                    filteredAutorisations: [],
                    facultes: [],
                    promotions: [],
                    filteredPromotions: [],
                    searchAutorisations: '',
                    loading: false,
                    loadingFacultes: false,
                    individualForm: {
                        etudiant_id: '',
                        salle_id: '',
                        date_debut: '',
                        date_fin: ''
                    },
                    groupForm: {
                        faculte: '',
                        promotion: '',
                        salle_id: '',
                        date_debut: '',
                        date_fin: ''
                    },
                    alert: {
                        show: false,
                        type: 'success',
                        message: ''
                    }
                }
            },
            mounted() {
                this.loadEtudiants();
                this.loadSalles();
                this.setDefaultDates();
            },
            methods: {
                async loadEtudiants() {
                    try {
                        const response = await fetch('../api/students.php');
                        const data = await response.json();
                        if (data.success) {
                            this.etudiants = data.students;
                        }
                    } catch (error) {
                        console.error('Erreur chargement étudiants:', error);
                    }
                },

                async loadSalles() {
                    try {
                        const response = await fetch('../api/salles.php');
                        const data = await response.json();
                        if (data.success) {
                            this.salles = data.salles;
                        }
                    } catch (error) {
                        console.error('Erreur chargement salles:', error);
                    }
                },

                async loadAutorisations() {
                    this.loading = true;
                    try {
                        const response = await fetch('../api/autorisations.php');
                        const data = await response.json();
                        if (data.success) {
                            this.autorisations = data.autorisations;
                            this.filteredAutorisations = [...this.autorisations];
                        }
                    } catch (error) {
                        console.error('Erreur chargement autorisations:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async loadFacultesFromUCB() {
                    this.loadingFacultes = true;
                    try {
                        const response = await fetch('https://akhademie.ucbukavu.ac.cd/api/v1/school/entity-main-list?entity_id=undefined&promotion_id=1&traditional=undefined');
                        const data = await response.json();
                        
                        if (data && data.data && data.data.entities) {
                            this.facultes = data.data.entities.map(entity => ({
                                id: entity.id,
                                name: entity.label || entity.title
                            }));
                        }
                        if (data && data.data && data.data.promotions) {
                            this.promotions = data.data.promotions.map(promotion => ({
                                id: promotion.id,
                                name: promotion.label || promotion.title,
                                entity_name: this.getEntityNameById(promotion.entityId)
                            }));
                        }
                        
                        this.showAlert('success', 'Données UCB chargées avec succès');
                    } catch (error) {
                        console.error('Erreur chargement UCB:', error);
                        this.showAlert('danger', 'Erreur lors du chargement des données UCB');
                    } finally {
                        this.loadingFacultes = false;
                    }
                },

                getEntityNameById(entityId) {
                    const entity = this.facultes.find(f => f.id === entityId);
                    return entity ? entity.name : '';
                },

                loadPromotions() {
                    if (this.groupForm.faculte) {
                        // Filtrer les promotions selon la faculté sélectionnée
                        const selectedFaculte = this.facultes.find(f => f.name === this.groupForm.faculte);
                        if (selectedFaculte) {
                            this.filteredPromotions = this.promotions.filter(promotion => 
                                promotion.entity_name === selectedFaculte.name
                            );
                        } else {
                            this.filteredPromotions = [];
                        }
                    } else {
                        this.filteredPromotions = [];
                    }
                    this.groupForm.promotion = '';
                },

                async saveIndividualAccess() {
                    this.loading = true;
                    try {
                        const response = await fetch('../api/autorisations.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                type: 'individual',
                                ...this.individualForm
                            })
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.showAlert('success', 'Accès individuel attribué avec succès');
                            this.resetIndividualForm();
                        } else {
                            this.showAlert('danger', data.message || 'Erreur lors de l\'attribution');
                        }
                    } catch (error) {
                        console.error('Erreur:', error);
                        this.showAlert('danger', 'Erreur de connexion');
                    } finally {
                        this.loading = false;
                    }
                },

                async saveGroupAccess() {
                    this.loading = true;
                    try {
                        const response = await fetch('../api/autorisations.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                type: 'group',
                                ...this.groupForm
                            })
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.showAlert('success', `Accès groupé attribué à ${data.count} étudiant(s)`);
                            this.resetGroupForm();
                        } else {
                            this.showAlert('danger', data.message || 'Erreur lors de l\'attribution groupée');
                        }
                    } catch (error) {
                        console.error('Erreur:', error);
                        this.showAlert('danger', 'Erreur de connexion');
                    } finally {
                        this.loading = false;
                    }
                },

                async revokeAccess(autorisation) {
                    if (!confirm(`Révoquer l'accès de ${autorisation.nom} ${autorisation.prenom} à ${autorisation.nom_salle} ?`)) {
                        return;
                    }

                    try {
                        const response = await fetch('../api/autorisations.php', {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ id: autorisation.id })
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.showAlert('success', 'Accès révoqué avec succès');
                            this.loadAutorisations();
                        } else {
                            this.showAlert('danger', data.message || 'Erreur lors de la révocation');
                        }
                    } catch (error) {
                        console.error('Erreur:', error);
                        this.showAlert('danger', 'Erreur de connexion');
                    }
                },

                filterAutorisations() {
                    if (!this.searchAutorisations) {
                        this.filteredAutorisations = [...this.autorisations];
                        return;
                    }

                    const term = this.searchAutorisations.toLowerCase();
                    this.filteredAutorisations = this.autorisations.filter(auth => 
                        auth.nom.toLowerCase().includes(term) ||
                        auth.prenom.toLowerCase().includes(term) ||
                        auth.matricule.toLowerCase().includes(term) ||
                        auth.nom_salle.toLowerCase().includes(term)
                    );
                },

                getStudentsCount() {
                    if (!this.groupForm.faculte || !this.groupForm.promotion) return 0;
                    
                    return this.etudiants.filter(etudiant => 
                        etudiant.faculte === this.groupForm.faculte && 
                        etudiant.promotion === this.groupForm.promotion
                    ).length;
                },

                setDefaultDates() {
                    const now = new Date();
                    const tomorrow = new Date(now);
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    const nextMonth = new Date(now);
                    nextMonth.setMonth(nextMonth.getMonth() + 1);

                    this.individualForm.date_debut = now.toISOString().slice(0, 16);
                    this.individualForm.date_fin = nextMonth.toISOString().slice(0, 16);
                    this.groupForm.date_debut = now.toISOString().slice(0, 16);
                    this.groupForm.date_fin = nextMonth.toISOString().slice(0, 16);
                },

                resetIndividualForm() {
                    this.individualForm = {
                        etudiant_id: '',
                        salle_id: '',
                        date_debut: '',
                        date_fin: ''
                    };
                    this.setDefaultDates();
                },

                resetGroupForm() {
                    this.groupForm = {
                        faculte: '',
                        promotion: '',
                        salle_id: '',
                        date_debut: '',
                        date_fin: ''
                    };
                    this.setDefaultDates();
                },

                formatDate(dateString) {
                    return new Date(dateString).toLocaleDateString('fr-FR', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
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