<h2>🔔 Connexion à la démo détectée</h2>

<p><strong>Utilisateur :</strong> {{ $user->username }}</p>
<p><strong>Email :</strong> {{ $user->email }}</p>
<p><strong>Type de démo :</strong> {{ $demo_type }}</p>
<p><strong>Date :</strong> {{ now()->format('d/m/Y H:i:s') }}</p>

<p>Visitez le site : <a href="{{ url('/') }}">{{ url('/') }}</a></p>
