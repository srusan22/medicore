# Medicore

Medicore je PHP web aplikacija za unos, spremanje i osnovnu analizu medicinskih nalaza. Aplikacija je napravljena kao početna verzija sustava koji će korisniku omogućiti upload PDF nalaza, usporedbu vrijednosti s referentnim rasponima i jednostavna objašnjenja rezultata pomoću AI agenta.

## Funkcionalnosti

- Login i registracija korisnika
- Dashboard s osnovnim pregledom aplikacije
- Navigacija: Pregled, Nalazi, Moj profil, Postavke
- Upload PDF nalaza
- Spremanje PDF nalaza u folder `nalazi`
- Prikaz spremljenih PDF nalaza po korisniku
- AI analiza vrijednosti prema referentnom rasponu
- Jednostavne preporuke za prehranu i stil života


## Demo prijava

Za testiranje aplikacije može se koristiti defaultni profil:

```txt
Korisničko ime: medicore_test
Lozinka: medicore_test
```

Na login/register stranici postoji i opcija:

```txt
Nastavi s Google računom
```

Trenutno je to demo simulacija Google prijave. Ne koristi pravi Google OAuth, nego lokalno prijavljuje demo korisnika `google_medicore`.

## Upload nalaza

Na stranici `Nalazi` korisnik može uploadati PDF nalaz. Aplikacija provjerava:

- da je datoteka PDF
- da datoteka ima PDF potpis `%PDF`
- da datoteka nije veća od 10 MB

Spremljeni nalazi nalaze se u folderu:

```txt
nalazi/
```

Naziv datoteke generira se automatski u obliku:

```txt
korisnik-datum-random.pdf
```

Primjer:

```txt
medicore_test-20260607-233437-a1b2c3d4.pdf
```

## Važne napomene

Ova verzija je prototip. Korisnici se spremaju lokalno u `data/users.json`, a nalazi se spremaju direktno u folder `nalazi`. Za produkcijsku verziju potrebno je dodati bazu podataka, pravi OAuth login, napredniju sigurnost uploadanih datoteka i stvarnu AI obradu nalaza.
