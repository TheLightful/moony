<?php
/*
Adaptation en php du fameux et excellent scripte Astro-MoonPhase de Brett Hamilton écrit en Perl.
http://search.cpan.org/~brett/Astro-MoonPhase-0.60/

Ce Scripte vous permettra de connaître, à une date donnée, l'illumination de la Lune, son age, 
sa distance en km par rapport à la Terre, son angle en degrés, sa distance par rapport au soleil, 
et son angle par rapport au soleil.

*/
class MoonStatic
     {
     function phase($Year, $Month, $Day, $Hour, $Minutes, $Seconds)
         {
         $DateSec = mktime($Hour, $Minutes, $Seconds, $Month, $Day, $Year);

         ini_set('precision', "20");   //Defini la precision des calcules

         # Astronomical constants.
         $Epoch                  = 2444238.5;        # 1980 January 0.0

         # Constants defining the Sun's apparent orbit.
         $Elonge                     = 278.833540;       # ecliptic longitude of the Sun at epoch 1980.0
         $Elongp                     = 282.596403;       # ecliptic longitude of the Sun at perigee
         $Eccent                     = 0.016718;             # eccentricity of Earth's orbit
         $Sunsmax                = 1.495985e8;       # semi-major axis of Earth's orbit, km
         $Sunangsiz              = 0.533128;             # sun's angular size, degrees, at semi-major axis distance

         # Elements of the Moon's orbit, epoch 1980.0.
         $Mmlong                     = 64.975464;        # moon's mean longitude at the epoch
         $Mmlongp                = 349.383063;       # mean longitude of the perigee at the epoch
         $Mlnode                     = 151.950429;       # mean longitude of the node at the epoch
         $Minc                   = 5.145396;             # inclination of the Moon's orbit
         $Mecc                   = 0.054900;             # eccentricity of the Moon's orbit
         $Mangsiz                = 0.5181;           # moon's angular size at distance a from Earth
         $Msmax                  = 384401.0;             # semi-major axis of Moon's orbit in km
         $Mparallax              = 0.9507;           # parallax at distance a from Earth
         $Synmonth               = 29.53058868;      # synodic month (new Moon to new Moon)

         $pdate = MoonStatic::jtime($DateSec);

         $pphase;                # illuminated fraction
         $mage;                  # age of moon in days
         $dist;                  # distance in kilometres
         $angdia;                # angular diameter in degrees
         $sudist;                # distance to Sun
         $suangdia;              # sun's angular diameter


         # Calculation of the Sun's position.

         $Day = $pdate - $Epoch;                                         # date within epoch
         $N = MoonStatic::fixangle((360 / 365.2422) * $Day);               # mean anomaly of the Sun
         $M = MoonStatic::fixangle($N + $Elonge - $Elongp);                # convert from perigee
                                         # co-ordinates to epoch 1980.0
         $Ec = MoonStatic::kepler($M, $Eccent);                            # solve equation of Kepler
         $Ec = sqrt((1 + $Eccent) / (1 - $Eccent)) * tan($Ec / 2);
         $Ec = 2 * MoonStatic::todeg(atan($Ec));                           # true anomaly
         $Lambdasun = MoonStatic::fixangle($Ec + $Elongp);                     # Sun's geocentric ecliptic
                                         # longitude
         # Orbital distance factor.
         $F = ((1 + $Eccent * cos(MoonStatic::torad($Ec))) / (1 - $Eccent * $Eccent));
         $SunDist = $Sunsmax / $F;                                   # distance to Sun in km
         $SunAng = $F * $Sunangsiz;                                  # Sun's angular size in degrees


         # Calculation of the Moon's position.

         # Moon's mean longitude.
         $ml = MoonStatic::fixangle(13.1763966 * $Day + $Mmlong);

         # Moon's mean anomaly.
         $MM = MoonStatic::fixangle($ml - 0.1114041 * $Day - $Mmlongp);

         # Moon's ascending node mean longitude.
         $MN = MoonStatic::fixangle($Mlnode - 0.0529539 * $Day);

         # Evection.
         $Ev = 1.2739 * sin(MoonStatic::torad(2 * ($ml - $Lambdasun) - $MM));

         # Annual equation.
         $Ae = 0.1858 * sin(MoonStatic::torad($M));

         # Correction term.
         $A3 = 0.37 * sin(MoonStatic::torad($M));

         # Corrected anomaly.
         $MmP = $MM + $Ev - $Ae - $A3;

         # Correction for the equation of the centre.
         $mEc = 6.2886 * sin(MoonStatic::torad($MmP));

         # Another correction term.
         $A4 = 0.214 * sin(MoonStatic::torad(2 * $MmP));

         # Corrected longitude.
         $lP = $ml + $Ev + $mEc - $Ae + $A4;

         # Variation.
         $V = 0.6583 * sin(MoonStatic::torad(2 * ($lP - $Lambdasun)));

         # True longitude.
         $lPP = $lP + $V;

         # Corrected longitude of the node.
         $NP = $MN - 0.16 * sin(MoonStatic::torad($M));

         # Y inclination coordinate.
         $y = sin(MoonStatic::torad($lPP - $NP)) * cos(MoonStatic::torad($Minc));

         # X inclination coordinate.
         $x = cos(MoonStatic::torad($lPP - $NP));

         # Ecliptic longitude.
         $Lambdamoon = MoonStatic::todeg(atan2($y, $x));
         $Lambdamoon += $NP;

         # Ecliptic latitude.
         $BetaM = MoonStatic::todeg(asin(sin(MoonStatic::torad($lPP - $NP)) * sin(MoonStatic::torad($Minc))));

         # Calculation of the phase of the Moon.

         # Age of the Moon in degrees.
         $MoonAge = $lPP - $Lambdasun;

         # Phase of the Moon.
         $MoonPhase = (1 - cos(MoonStatic::torad($MoonAge))) / 2;

         # Calculate distance of moon from the centre of the Earth.

         $MoonDist = ($Msmax * (1 - $Mecc * $Mecc)) /
             (1 + $Mecc * cos(MoonStatic::torad($MmP + $mEc)));

         # Calculate Moon's angular diameter.

         $MoonDFrac = $MoonDist / $Msmax;
         $MoonAng = $Mangsiz / $MoonDFrac;

         # Calculate Moon's parallax.

         $MoonPar = $Mparallax / $MoonDFrac;

         $pphase = $MoonPhase;                                   # illuminated fraction
         $mage = $Synmonth * (MoonStatic::fixangle($MoonAge) / 360.0);     # age of moon in days
         $dist = $MoonDist;                                      # distance in kilometres
         $angdia = $MoonAng;                                         # angular diameter in degrees
         $sudist = $SunDist;                                         # distance to Sun
         $suangdia = $SunAng;                                    # sun's angular diameter
         $mpfrac = MoonStatic::fixangle($MoonAge) / 360.0;
         return array( $pphase, $mage, $dist, $angdia, $sudist, $suangdia, $mpfrac, $mpfrac );
         }

     function fixangle($x)   { return ($x - 360.0 * (floor($x / 360.0))); }  # fix angle
     function torad($x)  { return ($x * (M_PI / 180.0)); }               # deg->rad
     function todeg($x)  { return ($x * (180.0 / M_PI)); }               # rad->deg

     function jtime($t)
         {
         $julian = ($t / 86400) + 2440587.5;     # (seconds /(seconds per day)) + julian date of epoch       2440587.5 / 86400 = 28,24753472222 Days
         return ($julian);
         }

     function kepler($m, $ecc)
         {
         $EPSILON = 1e-6;
         $delta = pow(10,-20000000);

         $m = MoonStatic::torad($m);
         $e = $m;
         while (abs($delta) > $EPSILON)
             {
             $delta = $e - $ecc * sin($e) - $m;
             $e -= $delta / (1 - $ecc * cos($e));
             }
         return ($e);
         }

     }


?>